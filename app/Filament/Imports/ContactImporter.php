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
            ImportColumn::make('company_id')
            ->label('Company ID')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('first_name')
            ->label('First Name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('last_name')
            ->label('Last Name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('phone1')
            ->label('Phone')
            ->castStateUsing(function (float $state): ?float {
                if (blank($state)) {
                    return null;
                }
            
                return round($state * 100);
            })
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('phone2')
            ->label('Phone 2')
                ->rules(['max:255']),
            ImportColumn::make('phone3')
            ->label('Phone 3')
                ->rules(['max:255']),
            ImportColumn::make('email')
                ->rules(['email', 'max:255']),
            ImportColumn::make('address')
                ->rules(['max:255']),
            ImportColumn::make('company')
                ->rules(['max:255']),
            ImportColumn::make('nationality')
                ->rules(['max:255']),
            ImportColumn::make('tag')
                ->rules(['max:255']),
        ];
    }

    public function resolveRecord(): ?Contact
    {
        return Contact::firstOrNew([
            // Update existing records, matching them by `$this->data['phone1']`
            'phone1' => $this->data['phone1'],
        ]);
        
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
