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
        // ── Local SMS bundles ─────────────────────────────────────────────
        $localBundles = [
            340   => ['sms' => 1000,   'label' => 'Starter',    'per_sms' => 'K0.34', 'highlight' => false, 'save' => null],
            1340  => ['sms' => 5000,   'label' => 'Bronze',     'per_sms' => 'K0.27', 'highlight' => false, 'save' => 'Save K360'],
            2000  => ['sms' => 9000,   'label' => 'Silver',     'per_sms' => 'K0.22', 'highlight' => false, 'save' => 'Save K1,060'],
            4750  => ['sms' => 25000,  'label' => 'Gold',       'per_sms' => 'K0.19', 'highlight' => true,  'save' => 'Save K3,750'],
            9000  => ['sms' => 50000,  'label' => 'Platinum',   'per_sms' => 'K0.18', 'highlight' => false, 'save' => 'Save K8,000'],
            17000 => ['sms' => 100000, 'label' => 'Enterprise', 'per_sms' => 'K0.17', 'highlight' => false, 'save' => 'Save K17,000'],
        ];

        // ── International SMS bundles ($0.389/SMS @ ~K27/USD) ─────────────
        $intlBundles = [
            1050  => ['sms' => 100,  'label' => 'Explorer', 'per_sms' => '$0.389', 'highlight' => false, 'save' => null],
            2625  => ['sms' => 250,  'label' => 'Connect',  'per_sms' => '$0.389', 'highlight' => false, 'save' => null],
            5250  => ['sms' => 500,  'label' => 'Global',   'per_sms' => '$0.389', 'highlight' => true,  'save' => null],
            10500 => ['sms' => 1000, 'label' => 'World',    'per_sms' => '$0.389', 'highlight' => false, 'save' => null],
        ];

        $buildCard = function ($bundle, $price, $routeName) {
            $sms     = number_format($bundle['sms']);
            $label   = $bundle['label'];
            $perSms  = $bundle['per_sms'];
            $save    = $bundle['save'];
            $popular = $bundle['highlight'];

            $saveBadge    = $save ? "<span style='background:#dcfce7;color:#166534;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:600;'>{$save}</span>" : '';
            $popularBadge = $popular ? "<span style='background:#fef9c3;color:#854d0e;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:600;margin-left:6px;'>⭐ Popular</span>" : '';
            $borderStyle  = $popular ? "border:2px solid #f59e0b;border-radius:12px;" : "border:1px solid #e5e7eb;border-radius:12px;";

            return Card::make([
                Placeholder::make("bundle_{$routeName}_{$price}")
                    ->label(new HtmlString("
                        <div style='{$borderStyle}padding:4px;'>
                            <div style='display:flex;align-items:center;gap:6px;flex-wrap:wrap;margin-bottom:8px;'>
                                <span style='font-size:13px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;'>{$label}</span>
                                {$popularBadge}
                            </div>
                            <span style='font-size:30px;font-weight:800;color:#111827;'>K{$price}</span>
                            <div style='margin:4px 0 8px;display:flex;align-items:center;gap:8px;'>
                                <span style='font-size:13px;color:#6b7280;'>{$perSms}/SMS</span>
                                {$saveBadge}
                            </div>
                        </div>
                    "))
                    ->content(new HtmlString("
                        <ul style='list-style:none;padding:0;margin:10px 0 0;'>
                            <li style='padding:5px 0;border-bottom:1px solid #f3f4f6;'>📨 <strong>{$sms} SMS</strong> credits</li>
                            <li style='padding:5px 0;border-bottom:1px solid #f3f4f6;'>♾️ Credits never expire</li>
                            <li style='padding:5px 0;'>✅ Delivery reports included</li>
                        </ul>
                    ")),
            ])->footerActions([
                Action::make("buy_{$routeName}_{$price}")
                    ->label('Buy Now')
                    ->button()
                    ->color($popular ? 'warning' : 'success')
                    ->url(fn () => route($routeName, ['amount' => encrypt($price)])),
            ])->columnSpan(1);
        };

        return $form->schema([

            // ── Local SMS ────────────────────────────────────────────────
            \Filament\Forms\Components\Section::make('📶 Local SMS — MTN, Airtel & Zamtel')
                ->description('Credits for sending to Zambian numbers via local networks. Balance shown as "SMS Credits" in your dashboard.')
                ->schema([
                    \Filament\Forms\Components\Grid::make(['default' => 1, 'md' => 2, 'lg' => 3])
                        ->schema(
                            collect($localBundles)
                                ->map(fn ($b, $p) => $buildCard($b, $p, 'subscription.lenco'))
                                ->toArray()
                        ),
                ]),

            // ── International SMS ─────────────────────────────────────────
            \Filament\Forms\Components\Section::make('🌍 International SMS — Any Country Worldwide')
                ->description('Credits for sending to international numbers via global routes. Includes free Sender ID and Flash SMS. $0.389/SMS flat rate.')
                ->schema([
                    \Filament\Forms\Components\Grid::make(['default' => 1, 'md' => 2, 'lg' => 4])
                        ->schema([
                            Placeholder::make('intl_features')
                                ->label('')
                                ->columnSpanFull()
                                ->content(new HtmlString("
                                    <div style='display:flex;gap:16px;flex-wrap:wrap;margin-bottom:4px;'>
                                        <span style='background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;padding:6px 14px;border-radius:20px;font-size:13px;font-weight:600;'>🌍 Any country</span>
                                        <span style='background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af;padding:6px 14px;border-radius:20px;font-size:13px;font-weight:600;'>👤 Free Sender ID</span>
                                        <span style='background:#fef9c3;border:1px solid #fde68a;color:#854d0e;padding:6px 14px;border-radius:20px;font-size:13px;font-weight:600;'>⚡ Flash SMS included</span>
                                        <span style='background:#fdf4ff;border:1px solid #e9d5ff;color:#7e22ce;padding:6px 14px;border-radius:20px;font-size:13px;font-weight:600;'>♾️ Credits never expire</span>
                                    </div>
                                ")),
                        ] + collect($intlBundles)
                                ->map(fn ($b, $p) => $buildCard($b, $p, 'subscription.international'))
                                ->toArray()
                        ),
                ]),

            // ── WhatsApp & Bulk Email subscriptions ───────────────────────
            \Filament\Forms\Components\Section::make('💬 WhatsApp & 📧 Bulk Email — Monthly Access')
                ->description('Unlock WhatsApp Business messaging and bulk email to contacts. K500/month each — includes 10 free sends on first sign-up.')
                ->schema([
                    \Filament\Forms\Components\Grid::make(['default' => 1, 'md' => 2])
                        ->schema([
                            Card::make([
                                Placeholder::make('whatsapp_subscription')
                                    ->label(new HtmlString("
                                        <div style='border:2px solid #25D366;border-radius:12px;padding:4px;'>
                                            <div style='display:flex;align-items:center;gap:8px;margin-bottom:8px;'>
                                                <span style='font-size:13px;font-weight:700;color:#25D366;text-transform:uppercase;letter-spacing:0.05em;'>WhatsApp Business</span>
                                                <span style='background:#fef9c3;color:#854d0e;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:600;'>⭐ Popular</span>
                                            </div>
                                            <span style='font-size:30px;font-weight:800;color:#111827;'>K500</span>
                                            <span style='font-size:14px;color:#6b7280;'>/month</span>
                                        </div>
                                    "))
                                    ->content(new HtmlString("
                                        <ul style='list-style:none;padding:0;margin:10px 0 0;'>
                                            <li style='padding:5px 0;border-bottom:1px solid #f3f4f6;'>💬 Meta WhatsApp Cloud API</li>
                                            <li style='padding:5px 0;border-bottom:1px solid #f3f4f6;'>📋 Approved message templates</li>
                                            <li style='padding:5px 0;border-bottom:1px solid #f3f4f6;'>👥 Bulk send to contacts</li>
                                            <li style='padding:5px 0;'>🎉 10 free sends on sign-up</li>
                                        </ul>
                                    ")),
                            ])->footerActions([
                                Action::make('subscribe_whatsapp')
                                    ->label('Subscribe — K500/month')
                                    ->button()
                                    ->color('success')
                                    ->url(fn () => route('subscription.whatsapp')),
                            ]),

                            Card::make([
                                Placeholder::make('email_subscription')
                                    ->label(new HtmlString("
                                        <div style='border:2px solid #4285F4;border-radius:12px;padding:4px;'>
                                            <div style='margin-bottom:8px;'>
                                                <span style='font-size:13px;font-weight:700;color:#4285F4;text-transform:uppercase;letter-spacing:0.05em;'>Bulk Email</span>
                                            </div>
                                            <span style='font-size:30px;font-weight:800;color:#111827;'>K500</span>
                                            <span style='font-size:14px;color:#6b7280;'>/month</span>
                                        </div>
                                    "))
                                    ->content(new HtmlString("
                                        <ul style='list-style:none;padding:0;margin:10px 0 0;'>
                                            <li style='padding:5px 0;border-bottom:1px solid #f3f4f6;'>📧 Send to all contacts with email</li>
                                            <li style='padding:5px 0;border-bottom:1px solid #f3f4f6;'>✉️ Your own SMTP (Gmail, Zoho…)</li>
                                            <li style='padding:5px 0;border-bottom:1px solid #f3f4f6;'>📝 Rich HTML composer</li>
                                            <li style='padding:5px 0;'>🎉 10 free sends on sign-up</li>
                                        </ul>
                                    ")),
                            ])->footerActions([
                                Action::make('subscribe_email')
                                    ->label('Subscribe — K500/month')
                                    ->button()
                                    ->color('primary')
                                    ->url(fn () => route('subscription.email')),
                            ]),
                        ]),
                ]),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (\Illuminate\Database\Eloquent\Builder $query) {
                // Super-admins see all payments; regular users see only their own.
                if (! auth()->user()->hasRole('super_admin')) {
                    $query->where('company_id', auth()->id());
                }
            })
            ->columns([
                Tables\Columns\TextColumn::make('reference')->searchable(),
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
