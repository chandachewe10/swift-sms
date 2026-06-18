<?php

namespace App\Filament\Resources\WhatsAppTemplateResource\Pages;

use App\Filament\Resources\WhatsAppTemplateResource;
use App\Models\WhatsAppConfig;
use App\Models\WhatsAppTemplate;
use App\Services\WhatsAppService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateWhatsAppTemplate extends CreateRecord
{
    protected static string $resource = WhatsAppTemplateResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $config = WhatsAppConfig::forUser(auth()->id());

        if (! $config) {
            Notification::make()
                ->title('WhatsApp phone number not registered')
                ->body('Go to WhatsApp → Register Phone Number to connect your company WhatsApp account first.')
                ->danger()->send();
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
            Notification::make()
                ->title('Meta API Error')
                ->body($result['message'] ?? 'Unknown error from WhatsApp API')
                ->danger()->send();
            $this->halt();
        }

        $template = WhatsAppTemplate::create([
            'user_id'              => auth()->id(),
            'name'                 => $data['name'],
            'category'             => $data['category'],
            'language'             => $data['language'],
            'body_text'            => $data['body_text'],
            'parameter_format'     => $format,
            'status'               => 'PENDING',
            'whatsapp_template_id' => $result['id'] ?? null,
        ]);

        Notification::make()
            ->title('Template submitted for Meta approval')
            ->body('Approval usually takes a few minutes. Use "Refresh Status" to check.')
            ->success()->send();

        return $template;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
