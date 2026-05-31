<?php

namespace App\Filament\Resources\WhatsAppConfigResource\Pages;

use App\Filament\Resources\WhatsAppConfigResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWhatsAppConfigs extends ListRecords
{
    protected static string $resource = WhatsAppConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Set Up Credentials'),
        ];
    }
}
