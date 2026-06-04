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
        // amount => [sms, label, per_sms, highlight, savings_label]
        $bundles = [
            800  => ['sms' => 1000,  'label' => 'Starter',    'per_sms' => 'K0.80', 'highlight' => false, 'save' => null],
            1500 => ['sms' => 2000,  'label' => 'Standard',   'per_sms' => 'K0.75', 'highlight' => false, 'save' => 'Save K100'],
            2100 => ['sms' => 3000,  'label' => 'Business',   'per_sms' => 'K0.70', 'highlight' => false, 'save' => 'Save K300'],
            3500 => ['sms' => 5000,  'label' => 'Growth',     'per_sms' => 'K0.70', 'highlight' => true,  'save' => 'Save K500'],
            5200 => ['sms' => 8000,  'label' => 'Pro',        'per_sms' => 'K0.65', 'highlight' => false, 'save' => 'Save K1,200'],
            6000 => ['sms' => 10000, 'label' => 'Enterprise', 'per_sms' => 'K0.60', 'highlight' => false, 'save' => 'Save K2,000'],
        ];

        return $form->schema([
            \Filament\Forms\Components\Grid::make([
                'default' => 1,
                'md' => 2,
                'lg' => 3,
            ])->schema(
                collect($bundles)->map(function ($bundle, $price) {
                    $sms      = number_format($bundle['sms']);
                    $label    = $bundle['label'];
                    $perSms   = $bundle['per_sms'];
                    $save     = $bundle['save'];
                    $popular  = $bundle['highlight'];

                    $saveBadge = $save
                        ? "<span style='background:#dcfce7;color:#166534;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:600;'>{$save}</span>"
                        : '';

                    $popularBadge = $popular
                        ? "<span style='background:#fef9c3;color:#854d0e;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:600;margin-left:6px;'>⭐ Most Popular</span>"
                        : '';

                    $borderStyle = $popular
                        ? "border:2px solid #f59e0b; border-radius:12px;"
                        : "border:1px solid #e5e7eb; border-radius:12px;";

                    return Card::make([
                        Placeholder::make("sms_{$price}")
                            ->label(new HtmlString("
                                <div style='{$borderStyle} padding:4px;'>
                                    <div style='display:flex;align-items:center;gap:6px;flex-wrap:wrap;margin-bottom:8px;'>
                                        <span style='font-size:13px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;'>{$label}</span>
                                        {$popularBadge}
                                    </div>
                                    <div style='display:flex;align-items:baseline;gap:4px;'>
                                        <span style='font-size:30px;font-weight:800;color:#111827;'>K{$price}</span>
                                    </div>
                                    <div style='margin:4px 0 8px;display:flex;align-items:center;gap:8px;'>
                                        <span style='font-size:13px;color:#6b7280;'>{$perSms}/SMS</span>
                                        {$saveBadge}
                                    </div>
                                </div>
                            "))
                            ->content(new HtmlString("
                                <ul style='list-style:none;padding:0;margin:12px 0 0;space-y:8px;'>
                                    <li style='padding:5px 0;border-bottom:1px solid #f3f4f6;'>📨 <strong>{$sms} SMS</strong> credits</li>
                                    <li style='padding:5px 0;border-bottom:1px solid #f3f4f6;'>🌍 Local &amp; international numbers</li>
                                    <li style='padding:5px 0;border-bottom:1px solid #f3f4f6;'>📶 MTN, Airtel &amp; Zamtel</li>
                                    <li style='padding:5px 0;'>♾️ No expiry — use at your pace</li>
                                </ul>
                            ")),
                    ])->footerActions([
                        Action::make("buy_{$price}")
                            ->label('Buy Now')
                            ->button()
                            ->color($popular ? 'warning' : 'success')
                            ->url(fn () => route('subscription.lenco', ['amount' => encrypt($price)])),
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
