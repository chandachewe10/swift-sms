<?php

namespace App\Filament\Resources\BulkContactEmailResource\Pages;

use App\Filament\Pages\EmailSubscriptionPage;
use App\Filament\Resources\BulkContactEmailResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBulkContactEmails extends ListRecords
{
    protected static string $resource = BulkContactEmailResource::class;

    public function mount(): void
    {
        parent::mount();

        $user = auth()->user();
        if (! $user?->hasRole('super_admin')
            && ! $user?->email_subscribed
            && ($user?->email_credits ?? 0) <= 0) {
            $this->redirect(EmailSubscriptionPage::getUrl());
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Send to All Contacts'),
        ];
    }
}
