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
        $config = WhatsAppConfig::where('user_id', auth()->id())->first();

        if (! $config) {
            Notification::make()
                ->title('WhatsApp credentials not configured')
                ->body('Go to WhatsApp → API Credentials to add your Meta credentials first.')
                ->danger()
                ->send();
            $this->halt();
        }

        $service = new WhatsAppService(
            $config->phone_number_id,
            $config->access_token,
            $config->business_account_id,
        );

        $result = $service->createTemplate([
            'name'       => $data['name'],
            'category'   => $data['category'],
            'language'   => $data['language'],
            'components' => [
                ['type' => 'BODY', 'text' => $data['body_text']],
            ],
        ]);

        if (isset($result['error'])) {
            Notification::make()
                ->title('Meta API Error')
                ->body($result['message'] ?? 'Unknown error from WhatsApp API')
                ->danger()
                ->send();
            $this->halt();
        }

        $template = WhatsAppTemplate::create([
            'user_id'              => auth()->id(),
            'name'                 => $data['name'],
            'category'             => $data['category'],
            'language'             => $data['language'],
            'body_text'            => $data['body_text'],
            'status'               => 'PENDING',
            'whatsapp_template_id' => $result['id'] ?? null,
        ]);

        Notification::make()
            ->title('Template submitted for Meta approval')
            ->body('Approval usually takes a few minutes. Use "Refresh Status" to check.')
            ->success()
            ->send();

        return $template;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
