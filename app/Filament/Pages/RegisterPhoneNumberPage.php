<?php

namespace App\Filament\Pages;

use App\Models\WhatsAppConfig;
use App\Models\WhatsAppPendingOnboarding;
use App\Services\MetaEmbeddedSignupService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
    public string $onboardUrl = '';

    public function mount(Request $request, MetaEmbeddedSignupService $service): void
    {
        $userId = auth()->id();
        $this->config = WhatsAppConfig::forUser($userId);

        // ── Handle OAuth redirect callback ────────────────────────────────────
        // Meta redirects the popup back to this page with ?code=...&state=...
        // The state token lets us resolve which pending session belongs to this user.
        $code  = $request->query('code');
        $state = $request->query('state');

        if ($code && ! $this->config) {
            // Resolve user via state token (definitive) or fall back to auth user
            $user = auth()->user();
            if ($state) {
                $pending = WhatsAppPendingOnboarding::where('state_token', $state)->first();
                if ($pending && $pending->user_id) {
                    $user = $pending->user ?? $user;
                }
            }

            $payload = [
                'code'                => $code,
                'waba_id'             => $request->query('waba_id'),
                'business_account_id' => $request->query('waba_id'),
                'business_id'         => $request->query('business_id'),
                'phone_number_id'     => $request->query('phone_number_id'),
                'phone_number'        => $request->query('phone_number'),
                'raw_payload'         => $request->query(),
            ];

            $result = $service->handle($user, $payload);

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

        // ── Always build the onboard URL so the button always works ─────────
        // Generate a unique state token regardless of whether a config already
        // exists — the user might want to re-connect or update their number.
        $stateToken = Str::uuid()->toString();

        WhatsAppPendingOnboarding::updateOrCreate(
            ['user_id' => $userId, 'status' => 'pending'],
            [
                'app_id'      => config('services.meta_whatsapp.app_id'),
                'state_token' => $stateToken,
            ]
        );

        $this->onboardUrl = static::buildOnboardUrl($stateToken);
    }

    /**
     * Build the Meta Embedded Signup URL, embedding the state token so Meta
     * echoes it back in the OAuth redirect for definitive user matching.
     */
    public static function buildOnboardUrl(string $stateToken = ''): string
    {
        $appId    = config('services.meta_whatsapp.app_id', '1573778724345266');
        $configId = config('services.meta_whatsapp.config_id', '866319909357587');

        $extras = urlencode(json_encode([
            'featureType'        => 'whatsapp_business_app_onboarding',
            'sessionInfoVersion' => '3',
            'version'            => 'v4',
        ]));

        $url = "https://business.facebook.com/messaging/whatsapp/onboard/?app_id={$appId}&config_id={$configId}&extras={$extras}";

        if ($stateToken) {
            $url .= '&state=' . urlencode($stateToken);
        }

        return $url;
    }

    /** @deprecated Use buildOnboardUrl() with a state token */
    public static function getOnboardUrl(): string
    {
        return static::buildOnboardUrl();
    }
}