<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class EmailSubscriptionPage extends Page
{
    protected static ?string $navigationGroup = 'Email';
    protected static ?string $navigationIcon  = 'heroicon-o-lock-closed';
    protected static ?string $navigationLabel = 'Unlock Email — K500';
    protected static ?string $title           = 'Bulk Email Messaging';
    protected static ?string $slug            = 'email-subscription';
    protected static ?int    $navigationSort  = 0;

    protected static string $view = 'filament.pages.email-subscription';

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return ! $user->email_subscribed
            && ($user->email_credits ?? 0) <= 0
            && ! $user->hasRole('super_admin');
    }

    public function mount(): void
    {
        if (auth()->user()?->email_subscribed || (auth()->user()?->email_credits ?? 0) > 0 || auth()->user()?->hasRole('super_admin')) {
            $this->redirect(route('filament.app.resources.bulk-contact-emails.index'));
        }
    }
}
