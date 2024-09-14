<?php

namespace App\Filament\Resources\SenderIdResource\Pages;

use App\Filament\Resources\SenderIdResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSenderIds extends ListRecords
{
    protected static string $resource = SenderIdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
