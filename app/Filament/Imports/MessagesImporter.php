<?php

namespace App\Filament\Imports;

use App\Models\Messages;
use App\Services\SmsDispatcher;
use App\Services\SmsSendSettlement;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Notifications\Notification;

class MessagesImporter extends Importer
{
    protected static ?string $model = Messages::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('company_id')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('message')
                ->requiredMapping()
                ->rules(['required', 'max:160']),
            ImportColumn::make('contact')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('status')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('responseText')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
        ];
    }

    public function resolveRecord(): ?Messages
    {
        $user = auth()->user();
        $contacts = sprintf('0%d', $this->data['contact']);
        $numbers  = [$contacts];
        $message  = $this->data['message'];

        $split = SmsDispatcher::splitByType($numbers);
        $local = count($split['local']);
        $intl  = count($split['international']);

        if ($local > 0 && $user->wallet->balance < $local) {
            static $notificationSent = false;
            if (! $notificationSent) {
                Notification::make()
                    ->title('Insufficient SMS Balance')
                    ->body('You have insufficient SMS balance to send the remaining SMS(es)')
                    ->warning()
                    ->send();
                $notificationSent = true;
            }

            return null;
        }

        if ($intl > 0 && ($user->international_sms_credits ?? 0) < $intl) {
            return null;
        }

        $result = SmsDispatcher::send($user->user_id, $numbers, $message, []);
        $settlement = SmsSendSettlement::settle($user, $result, $local, $intl);

        if (! $settlement['success']) {
            return null;
        }

        return Messages::create([
            'message'      => $message,
            'responseText' => $settlement['client_message'] ?: $result['responseText'],
            'contact'      => $contacts,
            'status'       => 200,
            'company_id'   => $user->user_id,
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your messages import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
