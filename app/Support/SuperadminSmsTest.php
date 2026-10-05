<?php

namespace App\Support;

use App\Models\Messages;
use App\Models\SenderId;
use App\Services\SmsDispatcher;
use Filament\Notifications\Notification;

class SuperadminSmsTest
{
    /**
     * Send a diagnostic SMS using the company's approved sender ID (no wallet debit).
     *
     * @return array{success: bool, message: string}
     */
    public static function send(SenderId $senderId, string $numbersRaw, string $message): array
    {
        if ((int) $senderId->getRawOriginal('is_approved') !== 1) {
            return [
                'success' => false,
                'message' => 'This sender ID is not approved yet. Approve it before testing delivery.',
            ];
        }

        $numbers = array_values(array_filter(array_map('trim', explode(',', $numbersRaw))));

        if ($numbers === []) {
            return [
                'success' => false,
                'message' => 'Enter at least one phone number (comma-separated).',
            ];
        }

        $message = trim($message);

        if ($message === '') {
            return [
                'success' => false,
                'message' => 'Message cannot be empty.',
            ];
        }

        $result = SmsDispatcher::send(
            $senderId->company_id,
            $numbers,
            $message,
        );

        Messages::create([
            'message'      => $message,
            'responseText' => '[Superadmin test] ' . $result['responseText'],
            'contact'      => implode(',', $numbers),
            'status'       => $result['statusCode'],
            'company_id'   => $senderId->company_id,
        ]);

        return [
            'success' => $result['success'],
            'message' => $result['responseText'],
        ];
    }

    public static function notifyResult(array $result): void
    {
        $notification = Notification::make()
            ->title($result['success'] ? 'Test SMS sent' : 'Test SMS failed')
            ->body($result['message']);

        if ($result['success']) {
            $notification->success()->send();
        } else {
            $notification->danger()->send();
        }
    }

    public static function testSendFormSchema(): array
    {
        return [
            \Filament\Forms\Components\Textarea::make('numbers')
                ->label('Phone numbers')
                ->required()
                ->rows(2)
                ->helperText('Comma-separated numbers (e.g. 0973750029,260973750029).'),
            \Filament\Forms\Components\Textarea::make('message')
                ->label('Message')
                ->required()
                ->rows(3)
                ->maxLength(640),
        ];
    }
}
