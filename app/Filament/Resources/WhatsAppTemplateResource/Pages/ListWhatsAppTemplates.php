<?php

namespace App\Filament\Resources\WhatsAppTemplateResource\Pages;

use App\Filament\Pages\WhatsAppSubscriptionPage;
use App\Filament\Resources\WhatsAppTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWhatsAppTemplates extends ListRecords
{
    protected static string $resource = WhatsAppTemplateResource::class;

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
            Actions\CreateAction::make()->label('New Template'),
        ];
    }
}
