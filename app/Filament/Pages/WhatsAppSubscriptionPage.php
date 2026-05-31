<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class WhatsAppSubscriptionPage extends Page
{
    protected static ?string $navigationGroup = 'WhatsApp';
    protected static ?string $navigationIcon  = 'heroicon-o-lock-closed';
    protected static ?string $navigationLabel = 'Unlock WhatsApp — K500';
    protected static ?string $title           = 'WhatsApp Business Messaging';
    protected static ?string $slug            = 'whatsapp-subscription';
    protected static ?int    $navigationSort  = 0;

    protected static string $view = 'filament.pages.whatsapp-subscription';

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return ! $user->whatsapp_subscribed
            && ($user->whatsapp_credits ?? 0) <= 0
            && ! $user->hasRole('super_admin');
    }

    public function mount(): void
    {
        if (auth()->user()?->whatsapp_subscribed || (auth()->user()?->whatsapp_credits ?? 0) > 0 || auth()->user()?->hasRole('super_admin')) {
            $this->redirect(route('filament.app.resources.whats-app-messages.index'));
        }
    }
}
