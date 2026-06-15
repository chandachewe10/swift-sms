<?php

namespace App\Filament\Pages;

use App\Models\SystemSetting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class SmsProviderSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationGroup = 'Developers';
    protected static ?string $navigationIcon  = 'heroicon-o-signal';
    protected static ?string $navigationLabel = 'SMS Provider';
    protected static ?string $title           = 'SMS Provider Settings';
    protected static ?int    $navigationSort  = 10;

    protected static string $view = 'filament.pages.sms-provider-settings';

    public ?array $data = [];

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public function mount(): void
    {
        if (! auth()->user()?->hasRole('super_admin')) {
            abort(403);
        }

        $this->form->fill([
            'sms_provider'     => SystemSetting::get('sms_provider', 'zamtel'),
            'mocean_api_token' => SystemSetting::get('mocean_api_token'),
            'development_mode' => SystemSetting::get('development_mode', 'false') === 'true',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([

                // ── Development Mode ──────────────────────────────────────────
                Forms\Components\Section::make('🧪 Development / Testing Mode')
                    ->description('When enabled, no real SMS messages are dispatched. Mocean\'s sandbox is used for all sends. Use this while onboarding clients or running tests.')
                    ->schema([
                        Forms\Components\Toggle::make('development_mode')
                            ->label('Enable Development Mode')
                            ->helperText('All SMS sends will be simulated — nothing reaches real phones.')
                            ->onColor('warning')
                            ->offColor('gray')
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                // Immediately persist so the banner updates on page refresh
                                SystemSetting::set('development_mode', $state ? 'true' : 'false');
                            }),

                        Forms\Components\Placeholder::make('dev_mode_notice')
                            ->label('')
                            ->content(new \Illuminate\Support\HtmlString("
                                <div style='background:#fef9c3;border:1px solid #fde68a;border-radius:10px;padding:14px 18px;display:flex;gap:12px;align-items:flex-start;'>
                                    <span style='font-size:22px;'>⚠️</span>
                                    <div>
                                        <strong style='color:#78350f;'>Development mode is ON</strong><br>
                                        <span style='color:#92400e;font-size:13px;'>
                                            All numbers (local &amp; international) are routed through <strong>Mocean</strong> using your configured API token — real messages are delivered.<br>
                                            Use your <strong>Mocean test API token</strong> to send real test messages without affecting production quotas.<br>
                                            Remember to switch to your live Mocean token and turn this off before going live.
                                        </span>
                                    </div>
                                </div>
                            "))
                            ->visible(fn (Forms\Get $get) => (bool) $get('development_mode')),
                    ]),

                // ── Provider Selection ────────────────────────────────────────
                Forms\Components\Section::make('Active SMS Provider')
                    ->description('Choose which gateway handles all outbound SMS — including API sends.')
                    ->schema([
                        Forms\Components\Select::make('sms_provider')
                            ->label('Provider')
                            ->options([
                                'zamtel' => 'Zamtel (default)',
                                'mocean' => 'Mocean (international)',
                            ])
                            ->required()
                            ->native(false)
                            ->live(),
                    ]),

                // ── Mocean Config ──────────────────────────────────────────────
                Forms\Components\Section::make('Mocean Configuration')
                    ->description('Required when Mocean is the active provider. Each company\'s approved Sender ID is used automatically.')
                    ->schema([
                        Forms\Components\TextInput::make('mocean_api_token')
                            ->label('Mocean API Token')
                            ->placeholder('eyJ...')
                            ->password()
                            ->revealable()
                            ->helperText('Generate from your Mocean Dashboard → API Account → Generate Token.')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (Forms\Get $get) => $get('sms_provider') === 'mocean'),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        SystemSetting::set('sms_provider',     $state['sms_provider']);
        SystemSetting::set('mocean_api_token', $state['mocean_api_token'] ?? null);
        SystemSetting::set('development_mode', ($state['development_mode'] ?? false) ? 'true' : 'false');

        $devLabel = ($state['development_mode'] ?? false) ? ' | ⚠️ Dev Mode ON' : '';

        Notification::make()
            ->title('Settings saved')
            ->body('Active provider: ' . strtoupper($state['sms_provider']) . $devLabel)
            ->success()
            ->send();
    }
}
