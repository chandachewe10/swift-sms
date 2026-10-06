<?php

namespace App\Services;

use App\Models\User;

class SmsSendSettlement
{
    public const QUEUED_CLIENT_MESSAGE = 'Your messages have been queued for delivery.';

    public static function shouldDebitLocal(int $localAttempted, int $localSent, int $localFailed): bool
    {
        return $localAttempted > 0 && ($localSent > 0 || $localFailed > 0);
    }

    /**
     * After SmsDispatcher::send(), debit credits and derive user-facing outcome.
     *
     * Local (Zamtel): debit all attempted local recipients when any were sent or queued for retry.
     * International: debit only successfully handed off to Mocean (unchanged).
     *
     * @return array{success: bool, client_message: string, debited_local: int, debited_intl: int}
     */
    public static function settle(User $user, array $result, int $localAttempted, int $intlAttempted): array
    {
        $localSent   = (int) ($result['localCount'] ?? 0);
        $localFailed = (int) ($result['localFailedCount'] ?? 0);
        $intlSent    = (int) ($result['internationalCount'] ?? 0);

        $localSettled = self::shouldDebitLocal($localAttempted, $localSent, $localFailed);
        $debitedLocal = 0;
        $debitedIntl  = 0;

        if ($localSettled) {
            $user->wallet->withdraw($localAttempted, [
                'description' => 'Local SMS (sent or queued via Zamtel)',
            ]);
            $debitedLocal = $localAttempted;
        }

        if ($intlSent > 0) {
            $user->decrement('international_sms_credits', $intlSent);
            $debitedIntl = $intlSent;
        }

        $success = (bool) ($result['success'] ?? false)
            || $localSettled
            || $intlSent > 0;

        $clientMessage = (string) ($result['clientFacingMessage'] ?? $result['responseText'] ?? '');

        if ($localFailed > 0) {
            $clientMessage = self::QUEUED_CLIENT_MESSAGE;
        }

        return [
            'success'         => $success,
            'client_message'  => $clientMessage,
            'debited_local'   => $debitedLocal,
            'debited_intl'    => $debitedIntl,
        ];
    }
}
