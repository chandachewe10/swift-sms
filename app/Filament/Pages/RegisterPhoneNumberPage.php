<?php

namespace App\Filament\Pages;

use App\Models\WhatsAppConfig;
use Filament\Pages\Page;

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

    public function mount(): void
    {
        $this->config = WhatsAppConfig::forUser(auth()->id());
    }

    public static function getOnboardUrl(): string
    {
        $appId = config('services.meta_whatsapp.app_id', '1573778724345266');
        $configId = config('services.meta_whatsapp.config_id', '866319909357587');

        $extras = urlencode(json_encode([
            'featureType' => 'whatsapp_business_app_onboarding',
            'sessionInfoVersion' => '3',
            'version' => 'v4',
            'features' => [
                [
                    'name' => 'app_only_install',
                ],
            ],
        ]));

        return "https://business.facebook.com/messaging/whatsapp/onboard/?app_id={$appId}&config_id={$configId}&extras={$extras}";
    }
}