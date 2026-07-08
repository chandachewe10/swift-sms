<?php

namespace App\Filament\Pages;

use App\Models\WhatsAppConfig;
use App\Models\WhatsAppPendingOnboarding;
use App\Services\MetaEmbeddedSignupService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Http\Request;

class RegisterPhoneNumberPage extends Page
{
    protected static ?string $navigationGroup = 'WhatsApp';
    protected static ?string $navigationIcon = 'heroicon-o-phone';
    protected static ?string $navigationLabel = 'Register Phone Number';
    protected static ?string $title = 'Register WhatsApp Phone Number';
    protected static ?string $slug = 'register-phone-number';
    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.register-phone-number';

    public ?WhatsAppConfig $config = null;
    public bool $justConnected = false;

    public function mount(Request $request, MetaEmbeddedSignupService $service): void
    {
        $userId = auth()->id();
        $this->config = WhatsAppConfig::forUser($userId);

        // Handle OAuth redirect callback: Meta redirects back to this page with
        // ?code=... after the user completes Embedded Signup.
        $code = $request->query('code');

        if ($code && ! $this->config) {
            $payload = [
                'code'                => $code,
                'waba_id'             => $request->query('waba_id'),
                'business_account_id' => $request->query('waba_id'),
                'business_id'         => $request->query('business_id'),
                'phone_number_id'     => $request->query('phone_number_id'),
                'phone_number'        => $request->query('phone_number'),
                'raw_payload'         => $request->query(),
            ];

            $result = $service->handle(auth()->user(), $payload);

            if ($result['success']) {
                $this->config = WhatsAppConfig::forUser($userId);
                $this->justConnected = true;

                Notification::make()
                    ->title('WhatsApp connected successfully!')
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('WhatsApp connection failed')
                    ->body($result['message'] ?? 'Please try again.')
                    ->danger()
                    ->send();
            }
        }

        // Ensure a pending onboarding session exists so the PARTNER_APP_INSTALLED
        // webhook can later be matched back to this user via their Meta business ID.
        if (! $this->config) {
            WhatsAppPendingOnboarding::updateOrCreate(
                ['user_id' => $userId, 'status' => 'pending'],
                ['app_id' => config('services.meta_whatsapp.app_id')]
            );
        }
    }

    public static function getOnboardUrl(): string
    {
        $appId    = config('services.meta_whatsapp.app_id', '1573778724345266');
        $configId = config('services.meta_whatsapp.config_id', '866319909357587');

        $extras = urlencode(json_encode([
            'featureType'        => 'whatsapp_business_app_onboarding',
            'sessionInfoVersion' => '3',
            'version'            => 'v4',
            'features'           => [
                ['name' => 'app_only_install'],
            ],
        ]));

        return "https://business.facebook.com/messaging/whatsapp/onboard/?app_id={$appId}&config_id={$configId}&extras={$extras}";
    }
}