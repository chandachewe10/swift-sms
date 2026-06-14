<?php

namespace App\Filament\Resources\BulkContactEmailResource\Pages;

use App\Filament\Resources\BulkContactEmailResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBulkContactEmails extends ListRecords
{
    protected static string $resource = BulkContactEmailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Send to All Contacts'),
        ];
    }
}
