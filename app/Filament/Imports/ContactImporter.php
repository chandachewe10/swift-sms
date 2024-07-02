<?php

namespace App\Filament\Imports;

use App\Models\Contact;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class ContactImporter extends Importer
{
    protected static ?string $model = Contact::class;

    public static function getColumns(): array
    {
        return [
            //
        ];
    }

    public function resolveRecord(): ?Contact
    {
        // return Contact::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Contact();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your contact import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
