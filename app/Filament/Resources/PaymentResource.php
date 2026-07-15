<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use App\Models\User;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section as FormSection;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    // ─────────────────────────────────────────────────────────────────────────
    // Form
    // ─────────────────────────────────────────────────────────────────────────

    public static function form(Form $form): Form
    {
        if (auth()->user()?->hasRole('super_admin')) {
            return static::adminForm($form);
        }

        return static::userForm($form);
    }

    /**
     * Admin manual-payment form.
     * Used for both Create (new record) and Edit (existing record) pages.
     * The "Subscription / Service" section is hidden during Edit because the
     * subscription was already activated when the payment was created.
     */
    public static function adminForm(Form $form): Form
    {
        $localOptions = [
            340   => '1,000 SMS — K340 (Starter)',
            1340  => '5,000 SMS — K1,340 (Bronze)',
            2000  => '9,000 SMS — K2,000 (Silver)',
            4750  => '25,000 SMS — K4,750 (Gold)',
            9000  => '50,000 SMS — K9,000 (Platinum)',
            17000 => '100,000 SMS — K17,000 (Enterprise)',
        ];

        $intlOptions = [
            1050  => '100 International SMS — K1,050',
            2625  => '250 International SMS — K2,625',
            5250  => '500 International SMS — K5,250',
            10500 => '1,000 International SMS — K10,500',
        ];

        return $form->schema([

            // ── Customer selector ─────────────────────────────────────────
            FormSection::make('Customer')
                ->icon('heroicon-o-user')
                ->schema([
                    Select::make('company_id')
                        ->label('Customer')
                        ->options(fn () => User::orderBy('name')->pluck('name', 'user_id'))
                        ->searchable()
                        ->preload()
                        ->required()
                        ->columnSpanFull(),
                ]),

            // ── Subscription type (create only) ───────────────────────────
            FormSection::make('Subscription / Service')
                ->icon('heroicon-o-shopping-bag')
                ->description('Select the service to activate after saving (only for Successful payments).')
                ->schema([
                    Select::make('subscription_type')
                        ->label('Service')
                        ->options([
                            'whatsapp'          => '💬 WhatsApp Business — K500/month',
                            'email'             => '📧 Bulk Email — K500/month',
                            'local_sms'         => '📶 Local SMS Credits',
                            'international_sms' => '🌍 International SMS Credits',
                        ])
                        ->live()
                        ->placeholder('— choose a service (optional) —')
                        ->afterStateUpdated(function (Set $set, ?string $state): void {
                            if (in_array($state, ['whatsapp', 'email'])) {
                                $set('amount', 500);
                                $set('transaction_amount', 500);
                            }
                            if (! $state || in_array($state, ['whatsapp', 'email'])) {
                                $set('sms_bundle', null);
                                $set('intl_bundle', null);
                            }
                        })
                        ->helperText('Choosing a service auto-fills the amount and activates the subscription on save.'),

                    Select::make('sms_bundle')
                        ->label('SMS Bundle')
                        ->options($localOptions)
                        ->live()
                        ->afterStateUpdated(function (Set $set, ?string $state): void {
                            if ($state) {
                                $set('amount', (int) $state);
                                $set('transaction_amount', (int) $state);
                            }
                        })
                        ->visible(fn (Get $get) => $get('subscription_type') === 'local_sms')
                        ->required(fn (Get $get) => $get('subscription_type') === 'local_sms'),

                    Select::make('intl_bundle')
                        ->label('International SMS Bundle')
                        ->options($intlOptions)
                        ->live()
                        ->afterStateUpdated(function (Set $set, ?string $state): void {
                            if ($state) {
                                $set('amount', (int) $state);
                                $set('transaction_amount', (int) $state);
                            }
                        })
                        ->visible(fn (Get $get) => $get('subscription_type') === 'international_sms')
                        ->required(fn (Get $get) => $get('subscription_type') === 'international_sms'),
                ])
                // Hide on edit — subscription was already activated when created.
                ->hidden(fn ($record) => $record !== null),

            // ── Payment details ────────────────────────────────────────────
            FormSection::make('Payment Details')
                ->icon('heroicon-o-credit-card')
                ->columns(2)
                ->schema([
                    TextInput::make('reference')
                        ->label('Payment Reference')
                        ->default(fn () => 'MANUAL-' . strtoupper(Str::random(8)))
                        ->required(),

                    TextInput::make('amount')
                        ->label('Amount (ZMW)')
                        ->numeric()
                        ->prefix('K')
                        ->required(),

                    TextInput::make('transaction_amount')
                        ->label('Total Transaction Amount')
                        ->numeric()
                        ->prefix('K')
                        ->helperText('Leave blank to copy Amount'),

                    TextInput::make('currency')
                        ->label('Currency')
                        ->default('ZMW')
                        ->required(),

                    TextInput::make('customer_wallet')
                        ->label('Customer Phone (Mobile Money)')
                        ->tel()
                        ->helperText('Optional'),

                    Select::make('status')
                        ->label('Payment Status')
                        ->options([
                            'successful' => '✅ Successful',
                            'pending'    => '⏳ Pending',
                            'failed'     => '❌ Failed',
                        ])
                        ->default('successful')
                        ->required()
                        ->helperText('Only "Successful" payments activate the subscription or credits'),

                    TextInput::make('depositId')
                        ->label('Lenco Transaction ID')
                        ->helperText('Optional — from Lenco\'s dashboard'),

                    TextInput::make('merchant_reference')
                        ->label('Merchant Reference')
                        ->helperText('Optional'),

                    TextInput::make('messages')
                        ->label('Description / Notes')
                        ->helperText('Auto-generated when creating via the Service selector above')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    /**
     * User-facing bundle catalog (read-only links to the Lenco checkout pages).
     * Preserved from the original resource; not a submission form.
     */
    public static function userForm(Form $form): Form
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

        // ── International SMS bundles ─────────────────────────────────────
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

            $saveBadge    = $save ? "<span class='bundle-save-badge' style='background:#dcfce7;color:#166534;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:600;'>{$save}</span>" : '';
            $popularBadge = $popular ? "<span class='bundle-popular-badge' style='background:#fef9c3;color:#854d0e;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:600;margin-left:6px;'>⭐ Popular</span>" : '';
            $borderClass  = $popular ? 'bundle-border-popular' : 'bundle-border';
            $borderStyle  = $popular ? 'border:2px solid #f59e0b;border-radius:12px;' : 'border:1px solid #e5e7eb;border-radius:12px;';

            return Card::make([
                Placeholder::make("bundle_{$routeName}_{$price}")
                    ->label(new HtmlString("
                        <div class='{$borderClass}' style='{$borderStyle}padding:4px;'>
                            <div style='display:flex;align-items:center;gap:6px;flex-wrap:wrap;margin-bottom:8px;'>
                                <span class='bundle-label' style='font-size:13px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;'>{$label}</span>
                                {$popularBadge}
                            </div>
                            <span class='bundle-price' style='font-size:30px;font-weight:800;color:#111827;'>K{$price}</span>
                            <div style='margin:4px 0 8px;display:flex;align-items:center;gap:8px;'>
                                <span class='bundle-muted' style='font-size:13px;color:#6b7280;'>{$perSms}/SMS</span>
                                {$saveBadge}
                            </div>
                        </div>
                    "))
                    ->content(new HtmlString("
                        <ul style='list-style:none;padding:0;margin:10px 0 0;'>
                            <li class='bundle-divider' style='padding:5px 0;border-bottom:1px solid #f3f4f6;'>📨 <strong>{$sms} SMS</strong> credits</li>
                            <li class='bundle-divider' style='padding:5px 0;border-bottom:1px solid #f3f4f6;'>♾️ Credits never expire</li>
                            <li style='padding:5px 0;'>✅ Delivery reports included</li>
                        </ul>
                    ")),
            ])->footerActions([
                FormAction::make("buy_{$routeName}_{$price}")
                    ->label('Buy Now')
                    ->button()
                    ->color($popular ? 'warning' : 'success')
                    ->url(fn () => route($routeName, ['amount' => encrypt($price)])),
            ])->columnSpan(1);
        };

        $darkModeStyles = new HtmlString('
            <style>
                .dark .bundle-price        { color: #f9fafb !important; }
                .dark .bundle-label        { color: #9ca3af !important; }
                .dark .bundle-muted        { color: #9ca3af !important; }
                .dark .bundle-border       { border-color: #374151 !important; }
                .dark .bundle-border-popular { border-color: #f59e0b !important; }
                .dark .bundle-divider      { border-bottom-color: #374151 !important; }
                .dark .bundle-save-badge   { background: #14532d !important; color: #86efac !important; }
                .dark .bundle-popular-badge { background: #78350f !important; color: #fde68a !important; }
                .dark .bundle-feature-green  { background: #052e16 !important; border-color: #166534 !important; color: #86efac !important; }
                .dark .bundle-feature-blue   { background: #0f2744 !important; border-color: #1d4ed8 !important; color: #93c5fd !important; }
                .dark .bundle-feature-yellow { background: #431407 !important; border-color: #92400e !important; color: #fde68a !important; }
                .dark .bundle-feature-purple { background: #2e1065 !important; border-color: #6b21a8 !important; color: #d8b4fe !important; }
                .dark .wa-billing-note      { background: #1c1409 !important; border-color: #92400e !important; }
                .dark .wa-billing-title     { color: #fbbf24 !important; }
                .dark .wa-billing-text      { color: #9ca3af !important; }
                .dark .wa-billing-strong    { color: #f9fafb !important; }
                .dark .wa-billing-row-border { border-bottom-color: #374151 !important; }
                .dark .wa-billing-price     { color: #f9fafb !important; }
            </style>
        ');

        return $form->schema([

            Placeholder::make('bundle_dark_mode_styles')
                ->label('')
                ->content($darkModeStyles)
                ->columnSpanFull(),

            // ── Local SMS ─────────────────────────────────────────────────
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
                                        <span class='bundle-feature-green' style='background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;padding:6px 14px;border-radius:20px;font-size:13px;font-weight:600;'>🌍 Any country</span>
                                        <span class='bundle-feature-blue' style='background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af;padding:6px 14px;border-radius:20px;font-size:13px;font-weight:600;'>👤 Free Sender ID</span>
                                        <span class='bundle-feature-yellow' style='background:#fef9c3;border:1px solid #fde68a;color:#854d0e;padding:6px 14px;border-radius:20px;font-size:13px;font-weight:600;'>⚡ Flash SMS included</span>
                                        <span class='bundle-feature-purple' style='background:#fdf4ff;border:1px solid #e9d5ff;color:#7e22ce;padding:6px 14px;border-radius:20px;font-size:13px;font-weight:600;'>♾️ Credits never expire</span>
                                    </div>
                                ")),
                        ] + collect($intlBundles)
                                ->map(fn ($b, $p) => $buildCard($b, $p, 'subscription.international'))
                                ->toArray()
                        ),
                ]),

            // ── WhatsApp & Bulk Email ─────────────────────────────────────
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
                                                <span class='bundle-popular-badge' style='background:#fef9c3;color:#854d0e;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:600;'>⭐ Popular</span>
                                            </div>
                                            <span class='bundle-price' style='font-size:30px;font-weight:800;color:#111827;'>K500</span>
                                            <span class='bundle-muted' style='font-size:14px;color:#6b7280;'>/month</span>
                                        </div>
                                    "))
                                    ->content(new HtmlString("
                                        <ul style='list-style:none;padding:0;margin:10px 0 0;'>
                                            <li class='bundle-divider' style='padding:5px 0;border-bottom:1px solid #f3f4f6;'>💬 Meta WhatsApp Cloud API</li>
                                            <li class='bundle-divider' style='padding:5px 0;border-bottom:1px solid #f3f4f6;'>📋 Approved message templates</li>
                                            <li class='bundle-divider' style='padding:5px 0;border-bottom:1px solid #f3f4f6;'>👥 Bulk send to contacts</li>
                                            <li style='padding:5px 0;'>🎉 10 free sends on sign-up</li>
                                        </ul>
                                        <div class='wa-billing-note' style='margin-top:14px;padding:10px 12px;border-radius:8px;background:#fffbeb;border:1px solid #fde68a;font-size:12px;'>
                                            <div class='wa-billing-title' style='font-weight:700;color:#b45309;margin-bottom:5px;'>How Billing Works</div>
                                            <p class='wa-billing-text' style='margin:0 0 5px;color:#6b7280;'><strong class='wa-billing-strong' style='color:#374151;'>K500/month</strong> covers SwiftSMS service fees — API, system, contact management &amp; embedded signup.</p>
                                            <p class='wa-billing-text' style='margin:0 0 8px;color:#6b7280;'>Message costs are billed <strong class='wa-billing-strong' style='color:#374151;'>directly by Meta</strong> based on usage. Rates for Zambian users:</p>
                                            <table style='width:100%;font-size:11px;border-collapse:collapse;'>
                                                <thead>
                                                    <tr class='wa-billing-row-border' style='border-bottom:1px solid #fde68a;'>
                                                        <th class='wa-billing-text' style='padding:2px 0;text-align:left;font-weight:600;color:#6b7280;'>Category</th>
                                                        <th class='wa-billing-text' style='padding:2px 0;text-align:right;font-weight:600;color:#6b7280;'>USD/message</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr class='wa-billing-row-border' style='border-bottom:1px solid #fef3c7;'>
                                                        <td class='wa-billing-text' style='padding:3px 0;color:#6b7280;'>Marketing</td>
                                                        <td class='wa-billing-price' style='padding:3px 0;text-align:right;font-weight:700;color:#374151;'>\$0.0225</td>
                                                    </tr>
                                                    <tr class='wa-billing-row-border' style='border-bottom:1px solid #fef3c7;'>
                                                        <td class='wa-billing-text' style='padding:3px 0;color:#6b7280;'>Utility</td>
                                                        <td class='wa-billing-price' style='padding:3px 0;text-align:right;font-weight:700;color:#374151;'>\$0.0040</td>
                                                    </tr>
                                                    <tr>
                                                        <td class='wa-billing-text' style='padding:3px 0;color:#6b7280;'>Authentication</td>
                                                        <td class='wa-billing-price' style='padding:3px 0;text-align:right;font-weight:700;color:#374151;'>\$0.0040</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    ")),
                            ])->footerActions([
                                FormAction::make('subscribe_whatsapp')
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
                                            <span class='bundle-price' style='font-size:30px;font-weight:800;color:#111827;'>K500</span>
                                            <span class='bundle-muted' style='font-size:14px;color:#6b7280;'>/month</span>
                                        </div>
                                    "))
                                    ->content(new HtmlString("
                                        <ul style='list-style:none;padding:0;margin:10px 0 0;'>
                                            <li class='bundle-divider' style='padding:5px 0;border-bottom:1px solid #f3f4f6;'>📧 Send to all contacts with email</li>
                                            <li class='bundle-divider' style='padding:5px 0;border-bottom:1px solid #f3f4f6;'>✉️ Your own SMTP (Gmail, Zoho…)</li>
                                            <li class='bundle-divider' style='padding:5px 0;border-bottom:1px solid #f3f4f6;'>📝 Rich HTML composer</li>
                                            <li style='padding:5px 0;'>🎉 10 free sends on sign-up</li>
                                        </ul>
                                    ")),
                            ])->footerActions([
                                FormAction::make('subscribe_email')
                                    ->label('Subscribe — K500/month')
                                    ->button()
                                    ->color('primary')
                                    ->url(fn () => route('subscription.email')),
                            ]),
                        ]),
                ]),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Table
    // ─────────────────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (\Illuminate\Database\Eloquent\Builder $query) {
                // Super-admins see all payments; regular users see only their own.
                if (! auth()->user()->hasRole('super_admin')) {
                    $query->where('company_id', auth()->user()->user_id);
                }
            })
            ->columns([
                Tables\Columns\TextColumn::make('company.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->visible(fn () => auth()->user()?->hasRole('super_admin')),

                Tables\Columns\TextColumn::make('messages')
                    ->label('Description')
                    ->searchable()
                    ->limit(45)
                    ->tooltip(fn ($record) => $record->messages),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->badge()
                    ->formatStateUsing(fn ($state) => 'K ' . number_format((float) $state, 2))
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'successful' => 'success',
                        'pending'    => 'warning',
                        'failed'     => 'danger',
                        default      => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('reference')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('download_receipt')
                        ->label('Download Receipt')
                        ->icon('heroicon-o-document-text')
                        ->color('info')
                        ->url(fn (Payment $record) => route('payment.receipt', $record))
                        ->openUrlInNewTab(),

                    Tables\Actions\EditAction::make()
                        ->visible(fn () => auth()->user()?->hasRole('super_admin')),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Pages
    // ─────────────────────────────────────────────────────────────────────────

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit'   => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
