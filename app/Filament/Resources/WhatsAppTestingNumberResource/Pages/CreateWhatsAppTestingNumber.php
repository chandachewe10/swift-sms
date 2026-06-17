<?php

namespace App\Filament\Resources\WhatsAppTestingNumberResource\Pages;

use App\Filament\Resources\WhatsAppTestingNumberResource;
use App\Models\WhatsAppTestingNumber;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateWhatsAppTestingNumber extends CreateRecord
{
    protected static string $resource = WhatsAppTestingNumberResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $phone = trim((string) ($data['phone_number'] ?? ''));

        $existing = WhatsAppTestingNumber::query()
            ->where('user_id', auth()->id())
            ->where('phone_number', $phone)
            ->first();

        if ($existing) {
            Notification::make()
                ->title('Number already submitted')
                ->body("Current status: {$existing->status}.")
                ->warning()
                ->send();

            $this->halt();
        }

        return WhatsAppTestingNumber::create([
            'user_id' => auth()->id(),
            'phone_number' => $phone,
            'status' => 'pending',
            'admin_note' => $data['admin_note'] ?? null,
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
