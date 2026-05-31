<?php

namespace App\Filament\Resources\EmailConfigResource\Pages;

use App\Filament\Resources\EmailConfigResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEmailConfig extends CreateRecord
{
    protected static string $resource = EmailConfigResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
