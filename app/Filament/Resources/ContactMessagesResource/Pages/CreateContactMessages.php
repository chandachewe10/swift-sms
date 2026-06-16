<?php

namespace App\Filament\Resources\ContactMessagesResource\Pages;

use App\Filament\Resources\ContactMessagesResource;
use App\Models\Contact;
use App\Models\Messages;
// SenderId resolved inside SmsDispatcher
use App\Services\SmsDispatcher;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateContactMessages extends CreateRecord
{
    protected static string $resource = ContactMessagesResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $companyId = auth()->user()->user_id;

        // ── Resolve which contacts to target ──────────────────────────────
        if ($data['send_to_all'] ?? false) {
            $contacts = Contact::where('company_id', $companyId)->get();
            $audienceLabel = 'All Contacts';

            if ($contacts->isEmpty()) {
                Notification::make()
                    ->title('No Contacts Found')
                    ->body('You have no contacts to send messages to.')
                    ->warning()
                    ->send();
                $this->halt();
            }

        } elseif (! empty($data['tag_filter'])) {
            $tag = $data['tag_filter'];
            $contacts = Contact::where('company_id', $companyId)
                ->where('tag', $tag)
                ->get();
            $audienceLabel = 'Tag: ' . $tag;

            if ($contacts->isEmpty()) {
                Notification::make()
                    ->title('No Contacts with Tag "' . $tag . '"')
                    ->body('No contacts were found with this tag. Check your contact tags and try again.')
                    ->warning()
                    ->send();
                $this->halt();
            }

        } else {
            $ids = $data['contact'] ?? [];

            if (empty($ids)) {
                Notification::make()
                    ->title('No Contacts Selected')
                    ->body('Please select contacts, choose a tag, or use "Send to All Contacts".')
                    ->warning()
                    ->send();
                $this->halt();
            }

            $contacts = Contact::findMany($ids);
            $audienceLabel = 'Selected Contacts';
        }
        
        // Filter contacts that have a phone number
        $contacts = $contacts->filter(fn ($c) => ! empty($c->phone1));

        if ($contacts->isEmpty()) {
            Notification::make()
                ->title('No Valid Phone Numbers')
                ->body('None of the selected contacts have valid phone numbers.')
                ->warning()
                ->send();
            $this->halt();
        }

        $user         = auth()->user();
        $message      = $data['message'];
        $contactStrings = $contacts->pluck('phone1')->values()->toArray();
        $contactCount = count($contactStrings);

        // Split numbers into local and international
        $split      = SmsDispatcher::splitByType($contactStrings);
        $localCount = count($split['local']);
        $intlCount  = count($split['international']);

        // Check local balance
        if ($localCount > 0 && $user->wallet->balance < $localCount) {
            $diff = $localCount - $user->wallet->balance;
            Notification::make()
                ->title('Insufficient Local SMS Balance')
                ->body("You need {$diff} more local SMS credit(s). Top up under Payments.")
                ->warning()->send();
            $this->halt();
        }

        // Check international balance
        if ($intlCount > 0 && ($user->international_sms_credits ?? 0) < $intlCount) {
            $diff = $intlCount - ($user->international_sms_credits ?? 0);
            Notification::make()
                ->title('Insufficient International SMS Balance')
                ->body("You need {$diff} more international SMS credit(s). Top up under Payments.")
                ->warning()->send();
            $this->halt();
        }

        $options = [
            'flash'    => (bool) ($data['flash_sms']   ?? false),
            'schedule' => $data['schedule_at'] ?? null,
        ];

        // ── Dispatch ──────────────────────────────────────────────────────
        // If the message contains {name}, send individually so each contact
        // gets their name substituted. Otherwise bulk-send all at once.
        $hasNamePlaceholder = str_contains($message, '{name}');

        if ($hasNamePlaceholder) {
            $successCount = 0;
            $failCount    = 0;
            $lastResponse = '';
            $totalLocal   = 0;
            $totalIntl    = 0;

            foreach ($contacts as $contact) {
                $personalised = str_replace(
                    '{name}',
                    trim(($contact->first_name ?? '') . ' ' . ($contact->last_name ?? '')),
                    $message
                );
                $r = SmsDispatcher::send($user->user_id, [$contact->phone1], $personalised, $options);
                if ($r['success']) {
                    $successCount++;
                    $totalLocal += $r['localCount'] ?? 0;
                    $totalIntl  += $r['internationalCount'] ?? 0;
                } else {
                    $failCount++;
                }
                $lastResponse = $r['responseText'];
            }

            $result = [
                'success'            => $successCount > 0,
                'responseText'       => "Personalised: {$successCount} sent, {$failCount} failed. Last: {$lastResponse}",
                'localCount'         => $totalLocal,
                'internationalCount' => $totalIntl,
            ];
        } else {
            $result = SmsDispatcher::send($user->user_id, $contactStrings, $message, $options);
        }

        $contactLogValue = match(true) {
            ($data['send_to_all'] ?? false) => 'All Contacts (' . $contactCount . ')',
            ! empty($data['tag_filter'])    => 'Tag: ' . $data['tag_filter'] . ' (' . $contactCount . ')',
            default                         => implode(',', $contactStrings),
        };

        $messageRecord = Messages::create([
            'message'      => $message,
            'responseText' => $result['responseText'],
            'contact'      => $contactLogValue,
            'status'       => $result['success'] ? 200 : 400,
            'company_id'   => $user->user_id,
        ]);

        if ($result['success']) {
            // Deduct local credits from wallet
            if ($result['localCount'] > 0) {
                $user->wallet->withdraw($result['localCount'], ['description' => 'Local SMS sent via Zamtel']);
            }
            // Deduct international credits from column
            if ($result['internationalCount'] > 0) {
                $user->decrement('international_sms_credits', $result['internationalCount']);
            }

            $recipientText = match(true) {
                ($data['send_to_all'] ?? false) => 'all contacts',
                ! empty($data['tag_filter'])    => $contactCount . ' contacts with tag "' . $data['tag_filter'] . '"',
                default                         => $contactCount . ' selected contacts',
            };

            $suffix = (! empty($options['schedule'])) ? ' Scheduled for ' . $data['schedule_at'] . '.' : '';

            Notification::make()
                ->title('Messages sent successfully')
                ->body("Delivered to {$recipientText}.{$suffix}")
                ->success()->send();
        } else {
            Notification::make()
                ->title('Failed to send messages')
                ->body($result['responseText'])
                ->danger()->send();
        }

        $this->halt();
        return $messageRecord;
    }
}