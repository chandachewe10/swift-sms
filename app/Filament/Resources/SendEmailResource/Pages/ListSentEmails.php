<?php

namespace App\Filament\Resources\SendEmailResource\Pages;

use App\Filament\Pages\EmailSubscriptionPage;
use App\Filament\Resources\SendEmailResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSentEmails extends ListRecords
{
    protected static string $resource = SendEmailResource::class;

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
            Actions\CreateAction::make()->label('Compose Email'),
        ];
    }
}
