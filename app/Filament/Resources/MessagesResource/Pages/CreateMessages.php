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

        $contactStrings = array_filter(
            array_map(
                fn ($c) => is_array($c) ? ($c['contact'] ?? '') : $c,
                $data['contact']
            )
        );

        if (empty($contactStrings)) {
            Notification::make()->title('No phone numbers entered')->warning()->send();
            $this->halt();
        }

        // Split into local and international before dispatching
        $split         = SmsDispatcher::splitByType(array_values($contactStrings));
        $localCount    = count($split['local']);
        $intlCount     = count($split['international']);

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
            // Deduct local credits from wallet
            if ($result['localCount'] > 0) {
                $user->wallet->withdraw($result['localCount'], ['description' => 'Local SMS sent via Zamtel']);
            }
            // Deduct international credits from column
            if ($result['internationalCount'] > 0) {
                $user->decrement('international_sms_credits', $result['internationalCount']);
            }

            $suffix = (! empty($options['schedule'])) ? ' Scheduled for ' . $data['schedule_at'] . '.' : '';
            Notification::make()
                ->title('Message(s) sent')
                ->body($result['responseText'] . $suffix)
                ->success()->send();
        } else {
            Notification::make()
                ->title('Failed to send message(s)')
                ->body($result['responseText'])
                ->danger()->send();
        }

        $this->halt();
        return $record;
    }
}
