<?php

namespace App\Filament\Resources\EmailConfigResource\Pages;

use App\Filament\Resources\EmailConfigResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmailConfig extends EditRecord
{
    protected static string $resource = EmailConfigResource::class;

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
