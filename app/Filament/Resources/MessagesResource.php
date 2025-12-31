<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MessagesResource\Pages;
use App\Filament\Resources\MessagesResource\RelationManagers;
use App\Filament\Imports\MessagesImporter;
use Filament\Tables\Actions\ImportAction;
use App\Models\Messages;
use App\Models\SenderId;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;
use Http;

class MessagesResource extends Resource
{
    protected static ?string $model = Messages::class;
    protected static ?string $navigationGroup = 'Messages';
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';
    protected static ?string $modelLabel = 'Send to Number';
    protected static ?int $navigationSort = 3; 

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('message')
                ->helperText('Write in not more than 160 characters')
                    ->minLength(2)
                    ->maxLength(160)
                    ->rows(5)
                    ->columnSpan(2),

                Forms\Components\Repeater::make('contact')
                    ->label('Phone Number(s)')
                    ->schema([
                        Forms\Components\TextInput::make('contact')
                            ->label('Phone')
                            ->prefixIcon('heroicon-o-phone')
                            ->required()
                            ->maxLength(255)
                            ->tel()
                            ->telRegex('/^(09|07)[5|6|7][0-9]{7}$/')


                    ])
                    ->columnSpan(2)
                    ->addActionLabel('Add Phone number')
                ,

                Forms\Components\TextInput::make('status')
                    ->hidden()
                    ->maxLength(255),
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
                ->url(asset('samples/messages.csv'))
                ->openUrlInNewTab(),
                
            Tables\Actions\Action::make('syncImport')
                ->label('Import Messages')
                ->icon('heroicon-o-arrow-up-tray')
                ->form([
                    Forms\Components\FileUpload::make('file')
                        ->acceptedFileTypes(['text/csv'])
                        ->helperText('Upload a CSV file with message data. Download the sample CSV to see the expected format.')
                        ->required(),
                ])
                ->action(function (array $data) {
                    try {
                        $filePath = storage_path('app/public/' . $data['file']);
                        
                        // Check if file exists
                        if (!file_exists($filePath)) {
                            Notification::make()
                                ->title('File Error')
                                ->body('The uploaded file could not be found.')
                                ->danger()
                                ->send();
                            return;
                        }
                        
                        $handle = fopen($filePath, 'r');
                        
                        if ($handle === false) {
                            Notification::make()
                                ->title('File Error')
                                ->body('Could not open the uploaded CSV file. Please check the file format.')
                                ->danger()
                                ->send();
                            return;
                        }
                        
                        $header = fgetcsv($handle);
                        $successCount = 0;
                        $errorCount = 0;
                        $insufficientBalance = false;
                        
                        if ($header === false) {
                            Notification::make()
                                ->title('CSV Error')
                                ->body('Could not read CSV headers. Please check if the file is a valid CSV.')
                                ->danger()
                                ->send();
                            fclose($handle);
                            return;
                        }
                        
                        // Validate CSV headers
                        $expectedHeaders = ['company_id', 'message', 'contact'];
                        $missingHeaders = array_diff($expectedHeaders, $header);
                        
                        if (!empty($missingHeaders)) {
                            Notification::make()
                                ->title('Invalid CSV Format')
                                ->body('Missing columns: ' . implode(', ', $missingHeaders))
                                ->danger()
                                ->send();
                            fclose($handle);
                            return;
                        }
                        
                        while (($row = fgetcsv($handle)) !== false) {
                            try {
                                $messageData = array_combine($header, $row);
                                
                                // Check if array_combine failed
                                if ($messageData === false) {
                                    $errorCount++;
                                    continue;
                                }
                                
                                // Validate required fields
                                if (empty($messageData['message']) || empty($messageData['contact'])) {
                                    $errorCount++;
                                    continue;
                                }
                                
                                // Validate message length
                                if (strlen($messageData['message']) > 160) {
                                    $errorCount++;
                                    continue;
                                }
                                
                                // Check user balance before processing
                                if (auth()->user()->wallet->balance < 1) {
                                    if (!$insufficientBalance) {
                                        Notification::make()
                                            ->title('Insufficient SMS Balance')
                                            ->body('You have insufficient SMS balance to send the remaining SMS(es)')
                                            ->warning()
                                            ->send();
                                        $insufficientBalance = true;
                                    }
                                    break; // Stop processing further messages
                                }
                                
                                // Format contact number
                                $contacts = sprintf('0%d', $messageData['contact']);
                                $senderId = SenderId::where('company_id',"=",auth()->user()->user_id)->where('is_approved',"=",1)->first()?->name;
                                
if (!$senderId) {
    Notification::make()
        ->title('No Approved Sender ID Found')
        ->body('Please configure a sender ID before sending messages.')
        ->danger()
        ->send();
    return;
}
                                $message = $messageData['message'];
                                $companyId = !empty($messageData['company_id']) ? $messageData['company_id'] : auth()->user()->user_id;
                                //dd($senderId);
                                // URL encode the components
                                $encodedContacts = urlencode($contacts);
                                $encodedSenderId = urlencode($senderId);
                                $encodedMessage = urlencode($message);
                                
                                // Construct the URL with properly encoded components
                                $url = env('BULK_SMS_BASE_URI') . '/api_key/' . urlencode(env('BULK_SMS_TOKEN')) . '/contacts/' . $encodedContacts . '/senderId/' . $encodedSenderId . '/message/' . $encodedMessage;
                                
                                // Send the HTTP request
                                $response = Http::timeout(30)->get($url);
                                
                                // Handle the response
                                $responseData = $response->json();
                                
                                if ($response->successful()) {
                                    // Withdraw the amount from the user's wallet
                                    auth()->user()->wallet->withdraw(1, ['description' => 'Sending of SMS via import']);
                                    
                                    // Create the message record
                                    Messages::create([
                                        'message' => $message,
                                        'responseText' => $responseData['responseText'] ?? '',
                                        'contact' => $contacts,
                                        'status' => $response->status(),
                                        'company_id' => $companyId,
                                    ]);
                                    
                                    $successCount++;
                                } else {
                                    $errorCount++;
                                    \Log::warning('SMS API Error: ' . $response->body());
                                }
                                
                            } catch (\Exception $rowException) {
                                $errorCount++;
                                \Log::warning('Error processing message row: ' . $rowException->getMessage());
                                continue; // Continue processing other rows
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
                        if ($successCount > 0 && $errorCount === 0 && !$insufficientBalance) {
                            Notification::make()
                                ->title('Messages Sent Successfully!')
                                ->body("{$successCount} messages sent successfully.")
                                ->success()
                                ->send();
                        } elseif ($successCount > 0 && ($errorCount > 0 || $insufficientBalance)) {
                            Notification::make()
                                ->title('Messages Partially Sent')
                                ->body("{$successCount} messages sent successfully. " . ($errorCount > 0 ? "{$errorCount} messages failed. " : "") . ($insufficientBalance ? "Stopped due to insufficient balance." : ""))
                                ->warning()
                                ->send();
                        } elseif ($successCount === 0 && $errorCount > 0) {
                            Notification::make()
                                ->title('No Messages Sent')
                                ->body("No messages were sent. {$errorCount} rows had errors. Please check your CSV format.")
                                ->danger()
                                ->send();
                        } elseif ($insufficientBalance && $successCount === 0) {
                            Notification::make()
                                ->title('Insufficient Balance')
                                ->body('No messages were sent due to insufficient SMS balance.')
                                ->warning()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('No Data Found')
                                ->body('The CSV file appears to be empty or contains no valid data rows.')
                                ->warning()
                                ->send();
                        }
                        
                    } catch (\Exception $e) {
                        // Catch any other unexpected errors
                        \Log::error('CSV Messages Import Error: ' . $e->getMessage());
                        
                        Notification::make()
                            ->title('Import Error')
                            ->body('An unexpected error occurred during import. Please try again or contact support.')
                            ->danger()
                            ->send();
                    }
                })
        ])
        ->modifyQueryUsing(function (Builder $query) { 
           
                return $query->where('company_id', auth()->user()->user_id); 
            
        }) 
            ->columns([
                Tables\Columns\TextColumn::make('message')
                    ->searchable(),
                Tables\Columns\TextColumn::make('contact')
                ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('responseText')
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
            ->recordUrl(null)
            ->recordAction(null)
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListMessages::route('/'),
            'create' => Pages\CreateMessages::route('/create'),
            'view' => Pages\ViewMessages::route('/{record}'),
            'edit' => Pages\EditMessages::route('/{record}/edit'),
        ];
    }
}