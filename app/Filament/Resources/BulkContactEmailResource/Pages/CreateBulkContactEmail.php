<?php

namespace App\Filament\Resources\BulkContactEmailResource\Pages;

use App\Filament\Pages\EmailSubscriptionPage;
use App\Filament\Resources\BulkContactEmailResource;
use App\Models\Contact;
use App\Models\EmailConfig;
use App\Models\EmailMessage;
use App\Services\EmailService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateBulkContactEmail extends CreateRecord
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

    protected function handleRecordCreation(array $data): Model
    {
        $user = auth()->user();
        $config = EmailConfig::where('user_id', auth()->id())->first();

        if (! $config) {
            Notification::make()
                ->title('SMTP not configured')
                ->body('Go to Email → SMTP Config to set up your mail server first.')
                ->danger()
                ->send();
            $this->halt();
        }

        if (! ($data['send_to_all_contacts'] ?? false)) {
            Notification::make()
                ->title('No recipients selected')
                ->body('Enable "Send to all contacts with email addresses" to choose your recipients.')
                ->warning()
                ->send();
            $this->halt();
        }

        $contacts = Contact::query()
            ->where('company_id', $user->user_id)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->when($data['contact_tag_filter'] ?? null, fn ($query, $tag) => $query->where('tag', $tag))
            ->get();

        if ($contacts->isEmpty()) {
            Notification::make()
                ->title('No contacts with email addresses found')
                ->body('Add email addresses to your contacts first, or adjust your tag filter.')
                ->warning()
                ->send();
            $this->halt();
        }

        if (! $user->hasRole('super_admin') && ! $user->email_subscribed) {
            $needed = $contacts->count();
            if (($user->email_credits ?? 0) < $needed) {
                $shortfall = $needed - ($user->email_credits ?? 0);
                Notification::make()
                    ->title('Insufficient email credits')
                    ->body("You need {$shortfall} more credit(s) to send to {$needed} contact(s). Subscribe for unlimited bulk email access.")
                    ->warning()
                    ->send();
                $this->halt();
            }
        }

        $service = new EmailService($config);
        $successCount = 0;
        $failCount = 0;
        $lastRecord = null;

        foreach ($contacts as $contact) {
            $success = $service->send($contact->email, $data['subject'], $data['body']);

            $lastRecord = EmailMessage::create([
                'user_id' => auth()->id(),
                'to_email' => $contact->email,
                'subject' => $data['subject'],
                'body' => $data['body'],
                'status' => $success ? 'sent' : 'failed',
                'error_message' => $success ? null : 'Send failed — check SMTP credentials.',
                'type' => 'bulk',
            ]);

            $success ? $successCount++ : $failCount++;
        }

        if (! $user->hasRole('super_admin') && ! $user->email_subscribed && $successCount > 0) {
            $user->decrement('email_credits', $successCount);
        }

        if ($failCount === 0) {
            Notification::make()
                ->title("Sent to {$successCount} contact(s) successfully")
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title("Sent: {$successCount} | Failed: {$failCount}")
                ->warning()
                ->send();
        }

        return $lastRecord;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
