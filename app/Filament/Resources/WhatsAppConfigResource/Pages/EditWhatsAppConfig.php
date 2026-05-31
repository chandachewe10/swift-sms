<?php

namespace App\Filament\Resources\WhatsAppConfigResource\Pages;

use App\Filament\Resources\WhatsAppConfigResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWhatsAppConfig extends EditRecord
{
    protected static string $resource = WhatsAppConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
