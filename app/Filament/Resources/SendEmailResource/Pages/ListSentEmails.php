<?php

namespace App\Filament\Resources\SendEmailResource\Pages;

use App\Filament\Resources\SendEmailResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSentEmails extends ListRecords
{
    protected static string $resource = SendEmailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Compose Email'),
        ];
    }
}
