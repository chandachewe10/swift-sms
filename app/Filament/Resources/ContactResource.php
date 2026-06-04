<?php

namespace App\Filament\Resources;

use App\Filament\Imports\ContactImporter;
use Filament\Tables\Actions\ImportAction;
use App\Filament\Resources\ContactResource\Pages;
use App\Filament\Resources\ContactResource\RelationManagers;
use App\Models\Contact;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContactResource extends Resource
{
    protected static ?string $model = Contact::class;
    protected static ?string $navigationGroup = 'Messages';
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $modelLabel = 'Add Contacts';
    protected static ?int $navigationSort = 1; 

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('company_id')
                    ->hidden()
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('first_name')
                    ->prefixIcon('heroicon-o-user')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('last_name')
                    ->prefixIcon('heroicon-o-user')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone1')
                    ->label('Primary Phone Number')
                    ->helperText('Include country code — e.g. 260973008909 for Zambia. This number will receive messages.')
                    ->placeholder('260973008909')
                    ->unique(ignoreRecord: true)
                    ->tel()
                    ->prefixIcon('heroicon-o-phone')
                    ->required()
                    ->maxLength(20),
                Forms\Components\TextInput::make('phone2')
                    ->tel()
                    ->label('Secondary Phone Number')
                    ->placeholder('260973008909')
                    ->helperText('Include country code, e.g. 260973008909')
                    ->prefixIcon('heroicon-o-phone')
                    ->maxLength(20)
                    ->default(null),
                Forms\Components\TextInput::make('phone3')
                    ->label('Emergency Phone Number')
                    ->tel()
                    ->placeholder('260973008909')
                    ->helperText('Include country code, e.g. 260973008909')
                    ->prefixIcon('heroicon-o-phone')
                    ->maxLength(20)
                    ->default(null),
                Forms\Components\TextInput::make('email')
                    ->prefixIcon('heroicon-o-envelope')
                    ->email()
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('address')
                    ->prefixIcon('heroicon-o-home')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('company')
                    ->prefixIcon('heroicon-o-building-office')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('nationality')
                    ->prefixIcon('heroicon-o-flag')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\Select::make('tag')
                    ->label('Tag')
                    ->searchable()
                    ->prefixIcon('heroicon-o-adjustments-vertical')
                    ->options([
                        'customer' => 'customer',
                        'admin' => 'admin',
                        'user' => 'user',
                        'moderator' => 'moderator',
                        'guest' => 'guest',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                Tables\Actions\Action::make('downloadSample')
                    ->label('Download Sample CSV')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->url(asset('samples/customers.csv'))
                    ->openUrlInNewTab(),
                    
                Tables\Actions\Action::make('syncImport')
                    ->label('Import Contacts')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->form([
                        Forms\Components\FileUpload::make('file')
                            ->acceptedFileTypes(['text/csv'])
                            ->helperText('Need help? Use the "Download Sample CSV" button above to see the expected format.')
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        try {
                            $filePath = storage_path('app/public/' . $data['file']);
                            
                            // Check if file exists
                            if (!file_exists($filePath)) {
                                \Filament\Notifications\Notification::make()
                                    ->title('File Error')
                                    ->body('The uploaded file could not be found.')
                                    ->danger()
                                    ->send();
                                return;
                            }
                            
                            $handle = fopen($filePath, 'r');
                            
                            if ($handle === false) {
                                \Filament\Notifications\Notification::make()
                                    ->title('File Error')
                                    ->body('Could not open the uploaded CSV file. Please check the file format.')
                                    ->danger()
                                    ->send();
                                return;
                            }
                            
                            $header = fgetcsv($handle);
                            $successCount = 0;
                            $errorCount = 0;
                            $errors = [];
                            
                            if ($header === false || empty($header)) {
                                \Filament\Notifications\Notification::make()
                                    ->title('CSV Error')
                                    ->body('Could not read CSV headers or file is empty. Please check if the file is a valid CSV.')
                                    ->danger()
                                    ->send();
                                fclose($handle);
                                return;
                            }
                            
                            // Clean and normalize headers
                            $header = array_map(function($h) {
                                return trim(strtolower(str_replace(' ', '_', $h)));
                            }, $header);
                            
                            // Map common header variations to expected field names
                            $headerMap = [
                                'company_id' => 'company_id',
                                'first_name' => 'first_name',
                                'firstname' => 'first_name',
                                'last_name' => 'last_name',
                                'lastname' => 'last_name',
                                'phone' => 'phone1',
                                'phone1' => 'phone1',
                                'phone_1' => 'phone1',
                                'primary_phone' => 'phone1',
                                'phone2' => 'phone2',
                                'phone_2' => 'phone2',
                                'secondary_phone' => 'phone2',
                                'phone3' => 'phone3',
                                'phone_3' => 'phone3',
                                'emergency_phone' => 'phone3',
                                'email' => 'email',
                                'address' => 'address',
                                'company' => 'company',
                                'nationality' => 'nationality',
                                'tag' => 'tag'
                            ];
                            
                            // Map headers to database fields
                            $mappedHeaders = [];
                            foreach ($header as $index => $headerName) {
                                if (isset($headerMap[$headerName])) {
                                    $mappedHeaders[$index] = $headerMap[$headerName];
                                }
                            }
                            
                            // Check if we have required headers
                            $requiredFields = ['first_name', 'last_name', 'phone1'];
                            $missingFields = [];
                            foreach ($requiredFields as $field) {
                                if (!in_array($field, $mappedHeaders)) {
                                    $missingFields[] = $field;
                                }
                            }
                            
                            if (!empty($missingFields)) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Missing Required Headers')
                                    ->body('The following required headers are missing: ' . implode(', ', $missingFields))
                                    ->danger()
                                    ->send();
                                fclose($handle);
                                return;
                            }
                            
                            $rowNumber = 1; // Start from 1 (header is row 0)
                            
                            while (($row = fgetcsv($handle)) !== false) {
                                $rowNumber++;
                                
                                try {
                                    // Skip completely empty rows
                                    if (empty(array_filter($row, function($value) { return !empty(trim($value)); }))) {
                                        continue;
                                    }
                                    
                                    // Build contact data using mapped headers
                                    $contactData = [];
                                    foreach ($mappedHeaders as $index => $fieldName) {
                                        $value = isset($row[$index]) ? trim($row[$index]) : null;
                                        // Convert empty strings to null to avoid unique constraint issues
                                        $contactData[$fieldName] = ($value === '' || $value === null) ? null : $value;
                                    }
                                    
                                    // Validate required fields
                                    $rowErrors = [];
                                    if (empty($contactData['first_name'])) {
                                        $rowErrors[] = 'First name is required';
                                    }
                                    if (empty($contactData['last_name'])) {
                                        $rowErrors[] = 'Last name is required';
                                    }
                                    if (empty($contactData['phone1'])) {
                                        $rowErrors[] = 'Phone number is required';
                                    }
                                    
                                    if (!empty($rowErrors)) {
                                        $errorCount++;
                                        $errors[] = "Row {$rowNumber}: " . implode(', ', $rowErrors);
                                        continue;
                                    }
                                    
                                    // Validate email format if provided
                                    if (!empty($contactData['email']) && !filter_var($contactData['email'], FILTER_VALIDATE_EMAIL)) {
                                        $errorCount++;
                                        $errors[] = "Row {$rowNumber}: Invalid email format";
                                        continue;
                                    }
                                    
                                    // Set default company_id if not provided
                                    if (empty($contactData['company_id'])) {
                                        $contactData['company_id'] = auth()->user()->user_id;
                                    }
                                    
                                    // Validate company_id is numeric
                                    if (!is_numeric($contactData['company_id'])) {
                                        $errorCount++;
                                        $errors[] = "Row {$rowNumber}: Company ID must be numeric";
                                        continue;
                                    }
                                    
                                    Contact::updateOrCreate(
                                        ['phone1' => $contactData['phone1']],
                                        [
                                            'company_id' => (int) $contactData['company_id'],
                                            'first_name' => $contactData['first_name'],
                                            'last_name' => $contactData['last_name'],
                                            'phone1' => $contactData['phone1'],
                                            'phone2' => !empty($contactData['phone2']) ? $contactData['phone2'] : null,
                                            'phone3' => !empty($contactData['phone3']) ? $contactData['phone3'] : null,
                                            'email' => !empty($contactData['email']) ? $contactData['email'] : null,
                                            'address' => !empty($contactData['address']) ? $contactData['address'] : null,
                                            'company' => !empty($contactData['company']) ? $contactData['company'] : null,
                                            'nationality' => !empty($contactData['nationality']) ? $contactData['nationality'] : null,
                                            'tag' => !empty($contactData['tag']) ? $contactData['tag'] : null,
                                        ]
                                    );
                                    
                                    $successCount++;
                                    
                                } catch (\Exception $rowException) {
                                    $errorCount++;
                                    $errors[] = "Row {$rowNumber}: " . $rowException->getMessage();
                                    \Log::warning("Error processing CSV row {$rowNumber}: " . $rowException->getMessage());
                                    continue;
                                }
                            }
                            
                            fclose($handle);
                            
                            // Clean up the uploaded file
                            try {
                                if (file_exists($filePath)) {
                                    unlink($filePath);
                                }
                            } catch (\Exception $cleanupException) {
                                \Log::warning('Could not delete uploaded file: ' . $cleanupException->getMessage());
                            }
                            
                            // Show appropriate notification based on results
                            if ($successCount > 0 && $errorCount === 0) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Import Completed Successfully!')
                                    ->body("{$successCount} contacts imported successfully.")
                                    ->success()
                                    ->send();
                            } elseif ($successCount > 0 && $errorCount > 0) {
                                $errorDetails = count($errors) > 5 ? 
                                    implode("\n", array_slice($errors, 0, 5)) . "\n... and " . (count($errors) - 5) . " more errors" :
                                    implode("\n", $errors);
                                    
                                \Filament\Notifications\Notification::make()
                                    ->title('Import Completed with Some Errors')
                                    ->body("{$successCount} contacts imported successfully. {$errorCount} rows had errors:\n\n{$errorDetails}")
                                    ->warning()
                                    ->send();
                            } elseif ($successCount === 0 && $errorCount > 0) {
                                $errorDetails = count($errors) > 5 ? 
                                    implode("\n", array_slice($errors, 0, 5)) . "\n... and " . (count($errors) - 5) . " more errors" :
                                    implode("\n", $errors);
                                    
                                \Filament\Notifications\Notification::make()
                                    ->title('Import Failed')
                                    ->body("No contacts were imported. {$errorCount} rows had errors:\n\n{$errorDetails}")
                                    ->danger()
                                    ->send();
                            } else {
                                \Filament\Notifications\Notification::make()
                                    ->title('No Data Found')
                                    ->body('The CSV file appears to be empty or contains no valid data rows.')
                                    ->warning()
                                    ->send();
                            }
                            
                        } catch (\Exception $e) {
                            \Log::error('CSV Import Error: ' . $e->getMessage());
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Import Error')
                                ->body('An unexpected error occurred during import: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
            ])
            ->modifyQueryUsing(function (Builder $query) { 
                return $query->where('company_id', auth()->user()->user_id); 
            }) 
            ->columns([
                Tables\Columns\TextColumn::make('company_id')
                    ->hidden()
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('first_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone1')
                    ->label('Phone number')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address'),
                Tables\Columns\TextColumn::make('tag')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContacts::route('/'),
            'create' => Pages\CreateContact::route('/create'),
            'view' => Pages\ViewContact::route('/{record}'),
            'edit' => Pages\EditContact::route('/{record}/edit'),
        ];
    }
}