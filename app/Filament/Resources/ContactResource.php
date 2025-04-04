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
                ->helperText('This is the phone number which will be recieving mesages')
                    ->unique(ignoreRecord: true)
                    ->tel()
                    ->prefixIcon('heroicon-o-phone')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone2')
                    ->tel()
                    ->label('Secondary Phone Number')
                    ->prefixIcon('heroicon-o-phone')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('phone3')
                ->label('Emergency Phone Number')
                    ->tel()
                    ->prefixIcon('heroicon-o-phone')
                    ->maxLength(255)
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
            ImportAction::make()
                ->importer(ContactImporter::class)
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
