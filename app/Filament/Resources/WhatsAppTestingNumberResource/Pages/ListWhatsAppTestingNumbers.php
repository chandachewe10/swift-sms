<?php

namespace App\Filament\Resources\WhatsAppTestingNumberResource\Pages;

use App\Filament\Resources\WhatsAppTestingNumberResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWhatsAppTestingNumbers extends ListRecords
{
    protected static string $resource = WhatsAppTestingNumberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Submit Testing Number'),
        ];
    }
}
