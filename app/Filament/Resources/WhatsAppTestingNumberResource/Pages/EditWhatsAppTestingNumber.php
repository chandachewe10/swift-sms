<?php

namespace App\Filament\Resources\WhatsAppTestingNumberResource\Pages;

use App\Filament\Resources\WhatsAppTestingNumberResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWhatsAppTestingNumber extends EditRecord
{
    protected static string $resource = WhatsAppTestingNumberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['phone_number'] = trim((string) ($data['phone_number'] ?? ''));

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
