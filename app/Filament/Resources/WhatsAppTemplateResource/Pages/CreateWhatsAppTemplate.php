<?php

namespace App\Filament\Resources\WhatsAppTemplateResource\Pages;

use App\Filament\Resources\WhatsAppTemplateResource;
use App\Models\User;
use App\Models\WhatsAppConfig;
use App\Models\WhatsAppTemplate;
use App\Services\WhatsAppService;
use App\Support\WhatsAppAdminAuthorization;
use App\Support\WhatsAppSubscriptionStatus;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateWhatsAppTemplate extends CreateRecord
{
    protected static string $resource = WhatsAppTemplateResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $actingAsAdmin = auth()->user()?->isSuperAdmin() ?? false;
        $owner         = WhatsAppAdminAuthorization::resolveOwnerUserId($data);
        $ownerUserId   = $owner['user_id'];

        if ($owner['error']) {
            Notification::make()
                ->title(match ($owner['error']) {
                    'forbidden'          => 'Not allowed',
                    'business_required'  => 'Business required',
                    'invalid_business' => 'Invalid business',
                    default              => 'Unable to continue',
                })
                ->body($owner['message'] ?? '')
                ->danger()
                ->send();
            $this->halt();
        }

        $config = WhatsAppConfig::forUser($ownerUserId);

        if (! $config || ! $config->isComplete()) {
            Notification::make()
                ->title('WhatsApp number not registered')
                ->body($actingAsAdmin
                    ? 'The selected business does not have a complete WhatsApp configuration.'
                    : 'You must register your own WhatsApp Business number before creating templates. Go to Register Phone Number to connect your account.')
                ->danger()
                ->persistent()
                ->send();
            $this->halt();
        }

        $format       = $data['parameter_format'] ?? 'positional';
        $exampleRows  = $data['example_params']   ?? [];

        // ── Build the body component with examples ─────────────────────────
        $bodyComponent = ['type' => 'BODY', 'text' => $data['body_text']];

        if (! empty($exampleRows)) {
            if ($format === 'named') {
                $bodyComponent['example'] = [
                    'body_text_named_params' => array_map(
                        fn ($row) => [
                            'param_name' => $row['param_name'],
                            'example'    => $row['example_value'],
                        ],
                        $exampleRows
                    ),
                ];
            } else {
                // Positional: [[value1, value2, ...]]
                $bodyComponent['example'] = [
                    'body_text' => [array_column($exampleRows, 'example_value')],
                ];
            }
        }

        $payload = [
            'name'             => $data['name'],
            'category'         => $data['category'],
            'language'         => $data['language'],
            'parameter_format' => strtoupper($format), // Meta expects NAMED / POSITIONAL
            'components'       => [$bodyComponent],
        ];

        $service = new WhatsAppService(
            $config->phone_number_id,
            $config->access_token,
            $config->business_account_id,
        );

        $result = $service->createTemplate($payload);

        if (isset($result['error'])) {
            $err   = $result['meta_error'] ?? [];
            $title = $err['error_user_title'] ?? 'Template rejected by Meta';
            $body  = WhatsAppService::friendlyError($err, 'Unknown error from WhatsApp API. Please check your template and try again.');

            Notification::make()
                ->title($title)
                ->body($body)
                ->danger()
                ->persistent()
                ->send();
            $this->halt();
        }

        $template = WhatsAppTemplate::create([
            'user_id'              => $ownerUserId,
            'name'                 => $data['name'],
            'category'             => $data['category'],
            'language'             => $data['language'],
            'body_text'            => $data['body_text'],
            'parameter_format'     => $format,
            'status'               => 'PENDING',
            'whatsapp_template_id' => $result['id'] ?? null,
        ]);

        $successBody = 'Approval usually takes a few minutes. Use "Refresh Status" to check.';
        $owner = User::find($ownerUserId);
        if ($actingAsAdmin && $owner && WhatsAppSubscriptionStatus::isExpired($owner)) {
            $successBody .= ' Note: this business\'s WhatsApp subscription payment is expired — they may need to renew after the template is approved.';
        }

        Notification::make()
            ->title('Template submitted for Meta approval')
            ->body($successBody)
            ->success()->send();

        return $template;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
