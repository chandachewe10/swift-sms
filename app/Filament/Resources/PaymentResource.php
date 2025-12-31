<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Actions\Action;
use Illuminate\Support\HtmlString;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    public static function form(Form $form): Form
    {
        // Bundles definition (amount => SMS count)
        $bundles = [
            500  => 1000,
            800  => 2000,
            1100 => 3000,
            1400 => 4000,
            1700 => 5000,
            2000 => 6000,
            2200 => 7000,
            2500 => 8000,
            3000 => 10000,
        ];

        return $form->schema([
            \Filament\Forms\Components\Grid::make([
                'default' => 1,
                'md' => 2,
                'lg' => 3,
            ])->schema(
                collect($bundles)->map(function ($sms, $price) {
                    return Card::make([
                        Placeholder::make("sms_$sms")
                            ->label(new HtmlString("<h2 class='text-xl font-bold'>K{$price}</h2>"))
                            ->content(new HtmlString("
                                <hr class='my-2 border-gray-300'>
                                <ul class='list-none pl-5 space-y-1'>
                                    <li>✔ {$sms} SMS</li>
                                    <li>✔ Validity: Until all SMS are consumed</li>
                                    <li>✔ Supported Networks: MTN, Airtel & Zamtel</li>
                                </ul>
                            ")),
                    ])->footerActions([
                        Action::make("buy_$sms")
                            ->label('Buy Now')
                            ->button()
                            ->color('success')
                            ->url(fn() => route('subscription.lenco', ['amount' => encrypt($price)])),
                    ])->columnSpan(1);
                })->toArray()
            ),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')->searchable(),
                //Tables\Columns\TextColumn::make('customer_wallet')->searchable(),
                Tables\Columns\TextColumn::make('amount')->badge()->numeric()->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([])
            ->actions([])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
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
