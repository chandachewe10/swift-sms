<?php

namespace App\Filament\Resources;

use App\Filament\Imports\MessagesImporter;
use App\Filament\Resources\MessagesResource\Pages;
use App\Filament\Resources\MessagesResource\RelationManagers;
use App\Models\Messages;
use App\Models\SmsTemplate;
use App\Services\MessagesCsvImportService;
use App\Services\SmsDispatcher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ImportAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
                Forms\Components\Select::make('template_id')
                    ->label('Use a Template (optional)')
                    ->placeholder('— pick a saved template —')
                    ->options(function () {
                        return SmsTemplate::where('company_id', auth()->user()->user_id)
                            ->orderBy('name')
                            ->pluck('name', 'id');
                    })
                    ->searchable()
                    ->native(false)
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        if ($state) {
                            $tpl = SmsTemplate::find($state);
                            if ($tpl) {
                                $set('message', $tpl->body);
                            }
                        }
                    })
                    ->columnSpan(2)
                    ->dehydrated(false),

                Forms\Components\Textarea::make('message')
                ->helperText('Write in not more than 160 characters. Use {placeholder} syntax for dynamic values.')
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
                            ->maxLength(20)
                            ->tel()
                            ->placeholder('260973008909')
                            ->helperText('Include country code — e.g. 260973008909 for Zambia, 254700000000 for Kenya'),
                    ])
                    ->columnSpan(2)
                    ->addActionLabel('Add Phone number')
                ,

                Forms\Components\TextInput::make('status')
                    ->hidden()
                    ->maxLength(255),

                // ── Mocean-only options ────────────────────────────────────
                Forms\Components\Section::make('Advanced Delivery Options')
                    ->description('Additional options available on your current messaging plan.')
                    ->icon('heroicon-o-signal')
                    ->schema([
                        Forms\Components\Toggle::make('flash_sms')
                            ->label('Flash SMS')
                            ->helperText('Message pops up immediately on the recipient\'s screen without being saved to their inbox.')
                            ->columnSpan(1),

                        Forms\Components\DateTimePicker::make('schedule_at')
                            ->label('Schedule Send')
                            ->helperText('Leave blank to send immediately. Uses your local time (UTC+2).')
                            ->minDate(now())
                            ->displayFormat('Y-m-d H:i')
                            ->native(false)
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->visible(fn () => SmsDispatcher::isMocean())
                    ->collapsible(),
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
                ->action(function (array $data, MessagesCsvImportService $importService) {
                    try {
                        $filePath = storage_path('app/public/' . $data['file']);

                        if (! file_exists($filePath)) {
                            Notification::make()
                                ->title('File Error')
                                ->body('The uploaded file could not be found.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $stats = $importService->processFile($filePath, auth()->user());

                        try {
                            if (file_exists($filePath)) {
                                unlink($filePath);
                            }
                        } catch (\Exception $cleanupException) {
                            \Log::warning('Could not delete uploaded file: ' . $cleanupException->getMessage());
                        }

                        if ($stats['aborted_no_sender']) {
                            Notification::make()
                                ->title('No Approved Sender ID Found')
                                ->body('Please configure a sender ID before sending messages.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $successCount = $stats['success_count'];
                        $queuedCount  = $stats['queued_count'];
                        $errorCount   = $stats['error_count'];

                        if ($errorCount > 0 && $successCount === 0 && ! $stats['insufficient_balance']) {
                            Notification::make()
                                ->title('Invalid CSV')
                                ->body('Could not import rows. Check headers (company_id, message, contact) and row data.')
                                ->danger()
                                ->send();

                            return;
                        }

                        if ($successCount > 0 && $queuedCount === 0 && $errorCount === 0 && ! $stats['insufficient_balance']) {
                            Notification::make()
                                ->title('Messages sent')
                                ->body("{$successCount} message(s) processed successfully.")
                                ->success()
                                ->send();
                        } elseif ($successCount > 0 && ($queuedCount > 0 || $errorCount > 0 || $stats['insufficient_balance'])) {
                            $parts = ["{$successCount} message(s) processed."];
                            if ($queuedCount > 0) {
                                $parts[] = "{$queuedCount} queued for delivery.";
                            }
                            if ($errorCount > 0) {
                                $parts[] = "{$errorCount} row(s) skipped due to errors.";
                            }
                            if ($stats['insufficient_balance']) {
                                $parts[] = 'Stopped due to insufficient balance.';
                            }
                            Notification::make()
                                ->title($queuedCount > 0 ? 'Messages queued' : 'Import partially completed')
                                ->body(implode(' ', $parts))
                                ->success()
                                ->send();
                        } elseif ($stats['insufficient_balance'] && $successCount === 0) {
                            Notification::make()
                                ->title('Insufficient balance')
                                ->body('No messages were sent due to insufficient SMS balance.')
                                ->warning()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('No data found')
                                ->body('The CSV file appears to be empty or contains no valid data rows.')
                                ->warning()
                                ->send();
                        }
                    } catch (\Exception $e) {
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