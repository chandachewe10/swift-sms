<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactMessagesResource\Pages;
use App\Filament\Resources\ContactMessagesResource\RelationManagers;
use App\Models\Contact;
use App\Models\Messages as ContactMessages;
use App\Services\SmsDispatcher;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ContactMessagesResource extends Resource
{
    protected static ?string $model = ContactMessages::class;

    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';
    protected static ?string $modelLabel = 'Send to Contacts';
    protected static ?string $navigationGroup = 'Messages';
    protected static ?int $navigationSort = 2;

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

                Checkbox::make('send_to_all')
                    ->label('Send to All Contacts')
                    ->helperText('Send to every contact in your address book')
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $set('tag_filter', null);
                            $set('contact', []);
                        }
                    })
                    ->columnSpan(2),

                Select::make('tag_filter')
                    ->label('Filter by Tag')
                    ->prefixIcon('heroicon-o-tag')
                    ->helperText('Send only to contacts that share this tag')
                    ->placeholder('Select a tag…')
                    ->options(function () {
                        return Contact::where('company_id', auth()->user()->user_id)
                            ->whereNotNull('tag')
                            ->where('tag', '!=', '')
                            ->distinct()
                            ->pluck('tag', 'tag')
                            ->sort()
                            ->toArray();
                    })
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $set('contact', []);
                        }
                    })
                    ->native(false)
                    ->searchable()
                    ->hidden(fn (callable $get) => (bool) $get('send_to_all'))
                    ->columnSpan(2),

                Select::make('contact')
                    ->prefixIcon('heroicon-o-users')
                    ->label('Contacts')
                    ->options(
                        Contact::where('company_id', auth()->user()->user_id)
                            ->get()
                            ->mapWithKeys(fn ($c) => [
                                $c->id => $c->first_name . ' ' . $c->last_name . ' — ' . $c->phone1
                                    . ($c->tag ? ' [' . $c->tag . ']' : ''),
                            ])
                    )
                    ->getSearchResultsUsing(function (string $search) {
                        return Contact::where('company_id', auth()->user()->user_id)
                            ->where(function ($q) use ($search) {
                                $q->where('first_name', 'like', "%{$search}%")
                                  ->orWhere('last_name',  'like', "%{$search}%")
                                  ->orWhere('phone1',     'like', "%{$search}%")
                                  ->orWhere('tag',        'like', "%{$search}%");
                            })
                            ->limit(50)
                            ->get()
                            ->mapWithKeys(fn ($c) => [
                                $c->id => $c->first_name . ' ' . $c->last_name . ' — ' . $c->phone1
                                    . ($c->tag ? ' [' . $c->tag . ']' : ''),
                            ])
                            ->toArray();
                    })
                    ->native(false)
                    ->multiple()
                    ->searchable()
                    ->searchingMessage('Searching for contacts…')
                    ->searchPrompt('Search by name, phone or tag')
                    ->loadingMessage('Loading contacts…')
                    ->hidden(fn (callable $get) => (bool) $get('send_to_all') || ! empty($get('tag_filter')))
                    ->required(fn (callable $get) => ! $get('send_to_all') && empty($get('tag_filter')))
                    ->columnSpan(2),

                // ── Mocean-only options ────────────────────────────────────
                Forms\Components\Section::make('Advanced Delivery Options')
                    ->description('Additional options available on your current messaging plan.')
                    ->icon('heroicon-o-signal')
                    ->schema([
                        Forms\Components\Toggle::make('flash_sms')
                            ->label('Flash SMS')
                            ->helperText('Message pops up on the recipient\'s screen immediately without being stored in their inbox.')
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
            'index' => Pages\ListContactMessages::route('/'),
            'create' => Pages\CreateContactMessages::route('/create'),
            'view' => Pages\ViewContactMessages::route('/{record}'),
            'edit' => Pages\EditContactMessages::route('/{record}/edit'),
        ];
    }
}