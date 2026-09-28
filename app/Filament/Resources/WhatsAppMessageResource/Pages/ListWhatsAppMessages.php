<?php

namespace App\Filament\Resources\WhatsAppMessageResource\Pages;

use App\Filament\Pages\WhatsAppSubscriptionPage;
use App\Filament\Resources\WhatsAppConfigResource;
use App\Filament\Resources\WhatsAppMessageResource;
use App\Models\User;
use App\Support\WhatsAppSubscriptionStatus;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWhatsAppMessages extends ListRecords
{
    protected static string $resource = WhatsAppMessageResource::class;

    public function mount(): void
    {
        parent::mount();

        $user = auth()->user();
        if (! $user?->hasRole('super_admin')
            && ! $user?->whatsapp_subscribed
            && ($user?->whatsapp_credits ?? 0) <= 0) {
            $this->redirect(WhatsAppSubscriptionPage::getUrl());
        }
    }

    protected function getHeaderActions(): array
    {
        $actions = [
            Actions\CreateAction::make()->label('Send Message'),
        ];

        if (auth()->user()?->hasRole('super_admin')) {
            $expiredCount = User::withCompleteWhatsAppConfig()
                ->get()
                ->filter(fn (User $user) => WhatsAppSubscriptionStatus::isExpired($user))
                ->count();

            if ($expiredCount > 0) {
                $actions[] = Actions\Action::make('expired_subscriptions')
                    ->label("{$expiredCount} expired WA subscription(s)")
                    ->color('danger')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->url(WhatsAppConfigResource::getUrl('index', [
                        'tableFilters' => ['subscription_status' => ['value' => 'expired']],
                    ]));
            }
        }

        return $actions;
    }
}
