<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactMessagesResource\Pages;
use App\Filament\Resources\ContactMessagesResource\RelationManagers;
use App\Models\Messages as ContactMessages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Checkbox;
use App\Models\Contact;

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
                    ->helperText('Check this to send the message to all your contacts')
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            // Clear the contact selection when "send to all" is checked
                            $set('contact', []);
                        }
                    })
                    ->columnSpan(2),

                Select::make('contact')
                    ->prefixIcon('heroicon-o-users')
                    ->label('Contacts')
                    ->options(
                        Contact::where('company_id', auth()->user()->user_id) 
                            ->get() 
                            ->mapWithKeys(function ($contact) { 
                                return [
                                    $contact->id => $contact->first_name . ' ' . $contact->last_name . ' - ' . $contact->phone1,
                                ];
                            })
                    )
                    ->getSearchResultsUsing(function (string $search) {
                        return Contact::where('company_id', auth()->user()->user_id)
                            ->where(function ($query) use ($search) {
                                $query->where('first_name', 'like', "%{$search}%")
                                    ->orWhere('last_name', 'like', "%{$search}%")
                                    ->orWhere('phone1', 'like', "%{$search}%");
                            })
                            ->limit(50)
                            ->get()
                            ->mapWithKeys(function ($contact) {
                                return [
                                    $contact->id => $contact->first_name . ' ' . $contact->last_name . ' - ' . $contact->phone1,
                                ];
                            })
                            ->toArray();
                    })
                    ->native(false)
                    ->multiple()
                    ->searchable()
                    ->searchingMessage('Searching for contacts...')
                    ->searchPrompt('Search contacts by their name, phone or email address')
                    ->loadingMessage('Loading Contacts...')
                    ->hidden(fn (callable $get) => $get('send_to_all')) // Hide when send_to_all is checked
                    ->required(fn (callable $get) => !$get('send_to_all')) // Required only when not sending to all
                    ->columnSpan(2),
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