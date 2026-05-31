<?php

namespace App\Filament\Resources\SendEmailResource\Pages;

use App\Filament\Pages\EmailSubscriptionPage;
use App\Filament\Resources\SendEmailResource;
use App\Models\EmailConfig;
use App\Models\EmailMessage;
use App\Services\EmailService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateSentEmail extends CreateRecord
{
    protected static string $resource = SendEmailResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $user = auth()->user();

        // Gate: block if no subscription and no credits
        if (! $user->hasRole('super_admin') && ! $user->email_subscribed && ($user->email_credits ?? 0) <= 0) {
            Notification::make()
                ->title('Email credits exhausted')
                ->body('Subscribe to Bulk Email (K300/month) to continue sending.')
                ->warning()
                ->send();
            $this->redirect(EmailSubscriptionPage::getUrl());
            $this->halt();
        }

        $config = EmailConfig::where('user_id', auth()->id())->first();

        if (! $config) {
            Notification::make()
                ->title('SMTP not configured')
                ->body('Go to Email → SMTP Config to add your mail server settings first.')
                ->danger()
                ->send();
            $this->halt();
        }

        $service = new EmailService($config);
        $success  = $service->send($data['to_email'], $data['subject'], $data['body']);

        $record = EmailMessage::create([
            'user_id'       => auth()->id(),
            'to_email'      => $data['to_email'],
            'subject'       => $data['subject'],
            'body'          => $data['body'],
            'status'        => $success ? 'sent' : 'failed',
            'error_message' => $success ? null : 'Send failed — check SMTP credentials.',
            'type'          => 'single',
        ]);

        // Deduct one credit for non-subscribers
        if ($success && ! $user->hasRole('super_admin') && ! $user->email_subscribed) {
            $user->decrement('email_credits');
        }

        if ($success) {
            Notification::make()
                ->title('Email sent successfully')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Failed to send email')
                ->body('Check your SMTP credentials in Email → SMTP Config.')
                ->danger()
                ->send();
        }

        return $record;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
