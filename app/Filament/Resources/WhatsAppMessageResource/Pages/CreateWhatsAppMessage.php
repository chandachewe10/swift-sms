<?php

namespace App\Filament\Resources\WhatsAppMessageResource\Pages;

use App\Filament\Resources\WhatsAppMessageResource;
use App\Models\WhatsAppConfig;
use App\Models\WhatsAppMessage;
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
        $config = WhatsAppConfig::where('user_id', auth()->id())->first();

        if (! $config) {
            Notification::make()
                ->title('WhatsApp credentials not configured')
                ->body('Go to WhatsApp → API Credentials to add your Meta credentials first.')
                ->danger()
                ->send();
            $this->halt();
        }

        $template = WhatsAppTemplate::findOrFail($data['whatsapp_template_id']);

        $service = new WhatsAppService(
            $config->phone_number_id,
            $config->access_token,
            $config->business_account_id,
        );

        $recipients = array_filter(
            array_column($data['recipients'] ?? [], 'phone'),
            fn (string $p) => ! empty(trim($p)),
        );

        if (empty($recipients)) {
            Notification::make()->title('No recipients provided')->warning()->send();
            $this->halt();
        }

        $successCount = 0;
        $failCount    = 0;
        $lastRecord   = null;

        foreach ($recipients as $phone) {
            $result = $service->sendMessage($phone, $template->name, $template->language);

            $lastRecord = WhatsAppMessage::create([
                'user_id'              => auth()->id(),
                'whatsapp_template_id' => $template->id,
                'recipient_phone'      => $phone,
                'status'               => isset($result['error']) ? 'failed' : 'queued',
                'whatsapp_message_id'  => $result['messages'][0]['id'] ?? null,
                'error_message'        => isset($result['error']) ? ($result['message'] ?? 'API error') : null,
            ]);

            isset($result['error']) ? $failCount++ : $successCount++;
        }

        if ($failCount === 0) {
            Notification::make()
                ->title("{$successCount} message(s) sent successfully")
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title("Sent: {$successCount} | Failed: {$failCount}")
                ->warning()
                ->send();
        }

        return $lastRecord;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
