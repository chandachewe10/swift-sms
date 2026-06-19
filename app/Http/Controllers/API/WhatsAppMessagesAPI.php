<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppConfig;
use App\Models\WhatsAppMessage;
use App\Models\WhatsAppTestingNumber;
use App\Models\WhatsAppTemplate;
use App\Services\WhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WhatsAppMessagesAPI extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'template_name' => ['required', 'string'],
            'language_code' => ['nullable', 'string'],
            'recipients' => ['required', 'array', 'min:1'],
            'recipients.*' => ['required', 'string'],
            'template_params' => ['nullable', 'array'],
            'template_params.*.param_name' => ['required_with:template_params', 'string'],
            'template_params.*.param_value' => ['required_with:template_params', 'string'],
        ]);

        $user = $request->user();
        ['config' => $config, 'using_admin' => $usingAdmin] = WhatsAppConfig::resolveForSending($user->id);

        if (! $config) {
            return response()->json([
                'success' => false,
                'message' => 'WhatsApp is not configured. Register your phone number or contact admin.',
            ], 422);
        }

        $template = WhatsAppTemplate::resolveApproved($validated['template_name'], $user->id);

        if (! $template) {
            return response()->json([
                'success' => false,
                'message' => 'Approved template not found. Use your own approved template or a shared testing template (opening_our_business_time, system_maintenance).',
            ], 422);
        }

        $isFreeTestingTemplate = WhatsAppTemplate::isSharedTestingTemplate($template->name);

        $recipients = array_values(array_filter(
            array_map('trim', $validated['recipients']),
            fn (string $phone) => $phone !== ''
        ));

        if (empty($recipients)) {
            return response()->json([
                'success' => false,
                'message' => 'No valid recipients provided.',
            ], 422);
        }

        if ($usingAdmin) {
            $approvedNumbers = WhatsAppTestingNumber::query()
                ->where('user_id', $user->id)
                ->where('status', 'approved')
                ->pluck('phone_number')
                ->map(fn (string $number) => trim($number))
                ->all();

            $unapprovedRecipients = array_values(array_diff($recipients, $approvedNumbers));
            if (! empty($unapprovedRecipients)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are using admin testing credentials. All recipients must be pre-approved testing numbers.',
                    'data' => [
                        'unapproved_recipients' => $unapprovedRecipients,
                    ],
                ], 422);
            }
        }

        if (! $isFreeTestingTemplate && ! $user->hasRole('super_admin') && ! $user->whatsapp_subscribed) {
            $available = (int) ($user->whatsapp_credits ?? 0);
            if ($available < count($recipients)) {
                $needed = count($recipients) - $available;
                return response()->json([
                    'success' => false,
                    'message' => "Insufficient WhatsApp credits. Need {$needed} more credit(s).",
                ], 422);
            }
        }

        $components = $this->buildComponents($validated['template_params'] ?? [], $template->parameter_format ?? 'positional');

        $service = new WhatsAppService(
            $config->phone_number_id,
            $config->access_token,
            $config->business_account_id
        );

        $languageCode = $validated['language_code'] ?? $template->language ?? 'en_US';
        $successCount = 0;
        $failCount = 0;
        $responses = [];

        foreach ($recipients as $phone) {
            $result = $service->sendMessage($phone, $template->name, $languageCode, $components);
            $failed = isset($result['error']);

            WhatsAppMessage::create([
                'user_id' => $user->id,
                'whatsapp_template_id' => $template->id,
                'recipient_phone' => $phone,
                'status' => $failed ? 'failed' : 'queued',
                'whatsapp_message_id' => $result['messages'][0]['id'] ?? null,
                'error_message' => $failed ? ($result['message'] ?? 'API error') : null,
            ]);

            if ($failed) {
                $failCount++;
            } else {
                $successCount++;
            }

            $responses[] = [
                'recipient' => $phone,
                'status' => $failed ? 'failed' : 'queued',
                'message_id' => $result['messages'][0]['id'] ?? null,
                'error' => $failed ? ($result['message'] ?? 'API error') : null,
            ];
        }

        if (! $isFreeTestingTemplate && ! $user->hasRole('super_admin') && ! $user->whatsapp_subscribed && $successCount > 0) {
            $user->decrement('whatsapp_credits', $successCount);
        }

        return response()->json([
            'success' => $failCount === 0,
            'message' => "Queued {$successCount} message(s); {$failCount} failed.",
            'data' => [
                'template' => $template->name,
                'language_code' => $languageCode,
                'sent' => $successCount,
                'failed' => $failCount,
                'results' => $responses,
            ],
        ], $failCount === 0 ? 202 : 207);
    }

    private function buildComponents(array $paramRows, string $format): array
    {
        if (empty($paramRows)) {
            return [];
        }

        $parameters = [];
        foreach ($paramRows as $row) {
            $parameter = [
                'type' => 'text',
                'text' => $row['param_value'] ?? '',
            ];

            if ($format === 'named') {
                $parameter['parameter_name'] = $row['param_name'] ?? '';
            }

            $parameters[] = $parameter;
        }

        return [[
            'type' => 'body',
            'parameters' => $parameters,
        ]];
    }
}
