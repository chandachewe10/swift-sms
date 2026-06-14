<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class EmailSubscriptionPage extends Page
{
    protected static ?string $navigationGroup = 'Email';
    protected static ?string $navigationIcon  = 'heroicon-o-lock-closed';
    protected static ?string $navigationLabel = 'Unlock Email — K300';
    protected static ?string $title           = 'Bulk Email Messaging';
    protected static ?string $slug            = 'email-subscription';
    protected static ?int    $navigationSort  = 0;

    protected static string $view = 'filament.pages.email-subscription';

    // Email is free — hide this subscription page from navigation entirely
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function mount(): void
    {
        // Redirect away — bulk email is now free for all users
        $this->redirect(route('filament.app.resources.send-emails.index'));
    }
}
