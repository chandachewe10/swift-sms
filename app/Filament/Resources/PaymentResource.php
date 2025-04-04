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
                        '500' => '1000',
                        '800' => '2000',
                        '1100' => '3000',
                        '1400' => '4000',
                        '1700' => '5000',
                        '2000' => '6000',
                        '2200' => '7000',
                        '2500' => '8000',
                    ])
                   
                    ->required(function ($state, Set $set) {
                        if ($state) {

                            $set('amount', $state);
                        }
                        return true;
                    }),

                    Forms\Components\Select::make('operator')
                ->label('Network Operator')
                    ->prefixIcon('heroicon-o-phone-arrow-down-left')
                    ->options([
                        'AIRTEL' => 'Airtel',
                        'MTN' => 'Mtn Zambia',
                        // 'ZAMTEL' => 'Zamtel',
                    ])
                   
                    ->required(),
                
               
                Forms\Components\TextInput::make('customer_wallet')
                ->prefixIcon('heroicon-o-phone')
                ->label('Enter Phone')
                    ->required()
                    ->regex('/^(09|07)[5|6|7][0-9]{7}$/'),
                Forms\Components\TextInput::make('amount')
                ->prefixIcon('heroicon-o-banknotes')
               
                ->numeric()
                    ->required()
                    ->readOnly()
                    ,
               
               
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->modifyQueryUsing(function (Builder $query) { 
           
            return $query->where('company_id', auth()->user()->user_id); 
        
    }) 
            ->columns([
                // Tables\Columns\TextColumn::make('merchant_reference')
                //     ->searchable(),
                Tables\Columns\TextColumn::make('company_id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('reference')
                    ->searchable(),
                Tables\Columns\TextColumn::make('currency')
                ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer_wallet')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                ->badge()
                    ->numeric()
                    ->sortable(),
                    Tables\Columns\TextColumn::make('status')
                    ->badge()
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
                //Tables\Actions\EditAction::make(),
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
