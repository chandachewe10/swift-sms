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

    // Fetch and format contacts
$contacts = Contact::all()->mapWithKeys(function($contact) {
    return [
        $contact->id => $contact->first_name . ' ' . $contact->last_name . ' - ' . $contact->phone1,
    ];
});

        return $form
            ->schema([
                Forms\Components\Textarea::make('message')
                ->helperText('Write in not more than 160 characters')
                    ->minLength(2)
                    ->maxLength(160)
                    ->rows(5)
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
    
    ->getSearchResultsUsing(fn (string $search) => Contact::where('first_name', 'like', "%{$search}%")
        ->orWhere('last_name', 'like', "%{$search}%")
        ->orWhere('phone1', 'like', "%{$search}%")
        ->limit(50)
        ->pluck('first_name', 'id')
        ->mapWithKeys(function($firstName, $id) {
            $contact = Contact::find($id);
            return [
                $id => $firstName . ' ' . $contact->last_name . ' - ' . $contact->phone1,
            ];
        })
        ->toArray()
    )
    ->native(false)
    ->multiple()
    ->searchable()
    ->searchingMessage('Searching for contacts...')
    ->searchPrompt('Search contacts by their name, phone or email address')
    ->loadingMessage('Loading Contacts...')
   
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
