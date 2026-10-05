<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SenderIdResource\Pages;
use App\Filament\Resources\SenderIdResource\RelationManagers;
use App\Models\SenderId;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SenderIdResource extends Resource
{
    protected static ?string $model = SenderId::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';
    //protected static ?string $modelLabel = 'Create SenderID';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('company_id')
                    ->label('Business')
                    ->options(fn () => User::orderBy('name')->pluck('name', 'user_id'))
                    ->searchable()
                    ->preload()
                    ->required(fn () => auth()->user()->hasRole('super_admin'))
                    ->visible(fn () => auth()->user()->hasRole('super_admin'))
                    ->helperText('Select the customer account this sender ID belongs to.'),
                Forms\Components\TextInput::make('name')
                ->prefixIcon('heroicon-o-user-group')
                ->label('Create Sender ID')
                ->helperText('This is the name which is going to appear to your clients when you send them the message. The name is supposed to be short with a maximum of 12 characters. Upon submitting it will then be approved before it is used. Spaces are removed automatically.')
                    ->required()
                    ->minLength(2)
                    ->maxLength(12)
                    ->dehydrateStateUsing(fn (?string $state) => SenderId::normalizeName($state))
                  ,
                Forms\Components\TextInput::make('company_phone')
                    ->required()
                    ->regex('/^(09|07)[5|6|7][0-9]{7}$/')
                    ->prefixIcon('heroicon-o-phone')
                    ->helperText('Other than your email we may use use this number to communicate with you, also when your SENDERID  is approved we will send a confirmation SMS to this number.'),
                   
                    Forms\Components\Select::make('is_approved')
                    ->label('Status')
                    ->prefixIcon('heroicon-o-shield-check')
                    ->options([
                        '1' => 'APPROVE',
                        '2' => 'LEAVE IT AS PROCESSING',
                        '3' => 'REJECT',
                        

                    ])

                    ->visible(fn() => auth()->user()->hasRole('super_admin'))
                    ,
            
            
            
                ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->modifyQueryUsing(function (Builder $query) { 
           
            if(!auth()->user()->hasRole('super_admin')){
            return $query->where('company_id', auth()->user()->user_id); 
        }
        
    }) 
            ->columns([
                Tables\Columns\TextColumn::make('company_id')
                   
                    ->sortable(),

                    Tables\Columns\TextColumn::make('username.name')
                   ->label('Owner')
                    ->sortable(),
                    Tables\Columns\TextColumn::make('company_phone')
                   
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('contacts_count')
                    ->label('Contacts')
                    ->counts('contacts')
                    ->sortable()
                    ->visible(fn () => auth()->user()?->hasRole('super_admin') ?? false),
                Tables\Columns\TextColumn::make('is_approved')

                    ->badge()
                    ->sortable(),
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
                Tables\Actions\Action::make('testSendSms')
                    ->label('Test SMS')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->visible(fn () => auth()->user()?->hasRole('super_admin') ?? false)
                    ->form(fn () => \App\Support\SuperadminSmsTest::testSendFormSchema())
                    ->action(function (SenderId $record, array $data): void {
                        \App\Support\SuperadminSmsTest::notifyResult(
                            \App\Support\SuperadminSmsTest::send($record, $data['numbers'], $data['message'])
                        );
                    }),
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
            RelationManagers\ContactsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSenderIds::route('/'),
            'create' => Pages\CreateSenderId::route('/create'),
            'view' => Pages\ViewSenderId::route('/{record}'),
            'edit' => Pages\EditSenderId::route('/{record}/edit'),
        ];
    }
}
