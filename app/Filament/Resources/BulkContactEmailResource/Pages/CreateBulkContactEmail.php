<?php

namespace App\Filament\Resources\BulkContactEmailResource\Pages;

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

    protected function handleRecordCreation(array $data): Model
    {
        $user   = auth()->user();
        $config = EmailConfig::where('user_id', auth()->id())->first();

        if (! $config) {
            Notification::make()
                ->title('SMTP not configured')
                ->body('Go to Email → SMTP Config to set up your mail server first.')
                ->danger()
                ->send();
            $this->halt();
        }

        $contacts = Contact::where('company_id', $user->user_id)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get();

        if ($contacts->isEmpty()) {
            Notification::make()
                ->title('No contacts with email addresses found')
                ->body('Add email addresses to your contacts first.')
                ->warning()
                ->send();
            $this->halt();
        }

        $service      = new EmailService($config);
        $successCount = 0;
        $failCount    = 0;
        $lastRecord   = null;

        foreach ($contacts as $contact) {
            $success = $service->send($contact->email, $data['subject'], $data['body']);

            $lastRecord = EmailMessage::create([
                'user_id'       => auth()->id(),
                'to_email'      => $contact->email,
                'subject'       => $data['subject'],
                'body'          => $data['body'],
                'status'        => $success ? 'sent' : 'failed',
                'error_message' => $success ? null : 'Send failed — check SMTP credentials.',
                'type'          => 'bulk',
            ]);

            $success ? $successCount++ : $failCount++;
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
