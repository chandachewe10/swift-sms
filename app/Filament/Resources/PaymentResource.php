<?php

namespace App\Filament\Resources;
use Filament\Forms\Set;
use Filament\Forms\Get;
use App\Filament\Resources\PaymentResource\Pages;
use App\Filament\Resources\PaymentResource\RelationManagers;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('number_of_sms')
                ->label('Number of SMSes')
                    ->prefixIcon('heroicon-o-wallet')
                    ->live()
                    ->options([
                        '300' => '0 - 1000',
                        '650' => '1,001 - 2000',
                        '1000' => '2,001 - 3000',
                        '1350' => '3,001 - 4000',
                        '1450' => '4,001 - 5000',
                        '1750' => '5,001 - 6000',
                        '2000' => '6,001 - 7000',
                        '2200' => '7,001 - 8000',
                    ])
                   
                    ->required(function ($state, Set $set) {
                        if ($state) {

                            $set('amount', $state);
                        }
                        return true;
                    }),

                    Forms\Components\Select::make('operator')
                ->label('Network Operator')
                    ->prefixIcon('heroicon-o-wallet')
                    ->options([
                        'AIRTEL' => 'Airtel',
                        'MTN' => 'Mtn Zambia',
                        'ZAMTEL' => 'Zamtel',
                    ])
                   
                    ->required(),
                
               
                Forms\Components\TextInput::make('customer_wallet')
                ->label('Enter Phone')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('amount')
                
                    ->required()
                    ->readOnly()
                    ,
               
               
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('merchant_reference')
                    ->searchable(),
                Tables\Columns\TextColumn::make('company_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reference')
                    ->searchable(),
                Tables\Columns\TextColumn::make('currency')
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer_wallet')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fee_amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('percentage')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('transaction_amount')
                    ->numeric()
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
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
