<?php

namespace App\Filament\Resources\EmailConfigResource\Pages;

use App\Filament\Resources\EmailConfigResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmailConfigs extends ListRecords
{
    protected static string $resource = EmailConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Set Up SMTP'),
        ];
    }
}
