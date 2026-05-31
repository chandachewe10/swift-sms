<?php

namespace App\Filament\Resources\WhatsAppMessageResource\Pages;

use App\Filament\Pages\WhatsAppSubscriptionPage;
use App\Filament\Resources\WhatsAppMessageResource;
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
        return [
            Actions\CreateAction::make()->label('Send Message'),
        ];
    }
}
