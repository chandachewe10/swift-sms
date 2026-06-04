<?php

namespace App\Filament\Pages;

use App\Models\SystemSetting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

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
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
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

        Notification::make()
            ->title('Settings saved')
            ->body('Active provider switched to: ' . strtoupper($state['sms_provider']))
            ->success()
            ->send();
    }
}
