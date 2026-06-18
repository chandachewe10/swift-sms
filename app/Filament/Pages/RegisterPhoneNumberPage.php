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
        return config('services.meta_whatsapp.onboard_url')
            ?? 'https://business.facebook.com/messaging/whatsapp/onboard/?app_id=1573778724345266&config_id=866319909357587&extras=%7B%22sessionInfoVersion%22%3A%223%22%2C%22version%22%3A%22v4%22%7D';
    }
}
