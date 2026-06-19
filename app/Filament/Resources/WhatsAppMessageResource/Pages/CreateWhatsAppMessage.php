<?php

namespace App\Filament\Resources\WhatsAppMessageResource\Pages;

use App\Filament\Resources\WhatsAppMessageResource;
use App\Models\Contact;
use App\Models\WhatsAppConfig;
use App\Models\WhatsAppMessage;
use App\Models\WhatsAppTestingNumber;
use App\Models\WhatsAppTemplate;
use App\Services\WhatsAppService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateWhatsAppMessage extends CreateRecord
{
    protected static string $resource = WhatsAppMessageResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $user = auth()->user();
        ['config' => $config, 'using_admin' => $usingAdmin] = WhatsAppConfig::resolveForSending($user->id);

        if (! $config) {
            Notification::make()
                ->title('WhatsApp not configured')
                ->body('No company or admin WhatsApp credentials are available yet. Please contact support.')
                ->danger()->send();
            $this->halt();
        }

        $template = WhatsAppTemplate::findOrFail($data['whatsapp_template_id']);
        $isFreeTestingTemplate = WhatsAppTemplate::isSharedTestingTemplate($template->name);

        $recipients = $this->resolveRecipients($data);

        if (empty($recipients)) {
            Notification::make()
                ->title('No recipients provided')
                ->body($data['send_to_all_contacts'] ?? false
                    ? 'No contacts with WhatsApp numbers match your selection.'
                    : 'Add at least one recipient or select contacts with WhatsApp numbers.')
                ->warning()->send();
            $this->halt();
        }

        // Credit check for non-subscribers (shared testing templates are free)
        if (! $isFreeTestingTemplate && ! $user->hasRole('super_admin') && ! $user->whatsapp_subscribed) {
            if (($user->whatsapp_credits ?? 0) < count($recipients)) {
                $needed = count($recipients) - ($user->whatsapp_credits ?? 0);
                Notification::make()
                    ->title('Insufficient WhatsApp credits')
                    ->body("You need {$needed} more credit(s) but have {$user->whatsapp_credits}.")
                    ->warning()->send();
                $this->halt();
            }
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
                Notification::make()
                    ->title('Unapproved testing recipient(s)')
                    ->body('Submit these number(s) under WhatsApp -> Testing Numbers and wait for admin approval: ' . implode(', ', $unapprovedRecipients))
                    ->danger()
                    ->send();
                $this->halt();
            }
        }

        $components = [];
        $paramRows = $data['template_params'] ?? [];
        $format = $template->parameter_format ?? 'positional';

        if (! empty($paramRows)) {
            if ($format === 'named') {
                $parameters = array_map(fn ($row) => [
                    'type' => 'text',
                    'parameter_name' => $row['param_name'],
                    'text' => $row['param_value'],
                ], $paramRows);
            } else {
                $parameters = array_map(fn ($row) => [
                    'type' => 'text',
                    'text' => $row['param_value'],
                ], $paramRows);
            }

            $components[] = [
                'type' => 'body',
                'parameters' => $parameters,
            ];
        }

        $service = new WhatsAppService(
            $config->phone_number_id,
            $config->access_token,
            $config->business_account_id,
        );

        $successCount = 0;
        $failCount = 0;
        $lastRecord = null;

        foreach ($recipients as $phone) {
            $result = $service->sendMessage($phone, $template->name, $template->language, $components);

            $lastRecord = WhatsAppMessage::create([
                'user_id' => auth()->id(),
                'whatsapp_template_id' => $template->id,
                'recipient_phone' => $phone,
                'status' => isset($result['error']) ? 'failed' : 'queued',
                'whatsapp_message_id' => $result['messages'][0]['id'] ?? null,
                'error_message' => isset($result['error']) ? ($result['message'] ?? 'API error') : null,
            ]);

            isset($result['error']) ? $failCount++ : $successCount++;
        }

        if (! $isFreeTestingTemplate && ! $user->hasRole('super_admin') && ! $user->whatsapp_subscribed && $successCount > 0) {
            $user->decrement('whatsapp_credits', $successCount);
        }

        if ($failCount === 0) {
            $suffix = $usingAdmin ? ' (sent via admin testing credentials)' : '';
            Notification::make()->title("{$successCount} message(s) sent successfully{$suffix}")->success()->send();
        } else {
            Notification::make()->title("Sent: {$successCount} | Failed: {$failCount}")->warning()->send();
        }

        return $lastRecord;
    }

    private function resolveRecipients(array $data): array
    {
        if ($data['send_to_all_contacts'] ?? false) {
            return Contact::query()
                ->where('company_id', auth()->user()->user_id)
                ->whereNotNull('phone2')
                ->where('phone2', '!=', '')
                ->when($data['contact_tag_filter'] ?? null, fn ($query, $tag) => $query->where('tag', $tag))
                ->pluck('phone2')
                ->map(fn (string $phone) => trim($phone))
                ->unique()
                ->values()
                ->all();
        }

        return array_values(array_filter(
            array_map('trim', array_column($data['recipients'] ?? [], 'phone')),
            fn (string $phone) => $phone !== ''
        ));
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
