<?php

namespace App\Filament\Resources\MessagesResource\Pages;

use App\Filament\Resources\MessagesResource;
use App\Models\Messages;
use App\Services\SmsDispatcher;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateMessages extends CreateRecord
{
    protected static string $resource = MessagesResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $user = auth()->user();

        // Build a flat array of phone number strings
        $contactStrings = array_filter(
            array_map(
                fn ($c) => is_array($c) ? ($c['contact'] ?? '') : $c,
                $data['contact']
            )
        );

        if (empty($contactStrings)) {
            Notification::make()
                ->title('No phone numbers entered')
                ->warning()
                ->send();
            $this->halt();
        }

        // Balance check
        if ($user->wallet->balance < count($contactStrings)) {
            $diff = count($contactStrings) - $user->wallet->balance;
            Notification::make()
                ->title('Insufficient SMS Balance')
                ->body("You need {$diff} more SMS credit(s).")
                ->warning()
                ->send();
            $this->halt();
        }

        $options = [
            'flash'    => (bool) ($data['flash_sms']   ?? false),
            'schedule' => $data['schedule_at'] ?? null,
        ];

        // ── Dispatch ──────────────────────────────────────────────────────
        $result = SmsDispatcher::send(
            $user->user_id,
            array_values($contactStrings),
            $data['message'],
            $options
        );

        $record = Messages::create([
            'message'      => $data['message'],
            'responseText' => $result['responseText'],
            'contact'      => implode(',', $contactStrings),
            'status'       => $result['success'] ? 200 : 400,
            'company_id'   => $user->user_id,
        ]);

        if ($result['success']) {
            $user->wallet->withdraw(
                count($contactStrings),
                ['description' => 'Sending SMS via ' . SmsDispatcher::activeProvider()]
            );

            $suffix = (! empty($options['schedule']))
                ? ' Scheduled for ' . $data['schedule_at'] . '.'
                : '';

            Notification::make()
                ->title('Message(s) sent')
                ->body($result['responseText'] . $suffix)
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Failed to send message(s)')
                ->body($result['responseText'])
                ->danger()
                ->send();
        }

        $this->halt();
        return $record;
    }
}
