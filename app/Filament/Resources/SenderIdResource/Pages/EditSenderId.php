<?php

namespace App\Filament\Resources\SenderIdResource\Pages;

use App\Filament\Resources\SenderIdResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSenderId extends EditRecord
{
    protected static string $resource = SenderIdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
