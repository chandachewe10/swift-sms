<?php

namespace App\Support;

use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;

class WhatsAppSubscriptionStatus
{
    public const PAYMENT_DESCRIPTION_PATTERN = 'WhatsApp Business subscription%';

    public static function latestPayment(User $user): ?Payment
    {
        return Payment::query()
            ->where('company_id', $user->user_id)
            ->where('status', 'successful')
            ->where('messages', 'like', self::PAYMENT_DESCRIPTION_PATTERN)
            ->latest('created_at')
            ->first();
    }

    public static function expiresAt(User $user): ?Carbon
    {
        $payment = self::latestPayment($user);

        return $payment?->created_at?->copy()->addMonth();
    }

    public static function hasActivePayment(User $user): bool
    {
        $expiresAt = self::expiresAt($user);

        return $expiresAt !== null && $expiresAt->isFuture();
    }

    public static function isExpired(User $user): bool
    {
        $payment = self::latestPayment($user);

        if ($payment) {
            return ! self::hasActivePayment($user);
        }

        return (bool) $user->whatsapp_subscribed;
    }

    public static function labelForUser(User $user): string
    {
        if (self::hasActivePayment($user)) {
            $until = self::expiresAt($user)?->format('M j, Y');

            return "{$user->name} (paid until {$until})";
        }

        if (self::isExpired($user)) {
            $payment = self::latestPayment($user);
            if ($payment) {
                $last = $payment->created_at->format('M j, Y');

                return "{$user->name} — subscription expired (last paid {$last})";
            }

            return "{$user->name} — subscription expired (no payment on record)";
        }

        return "{$user->name} — no WhatsApp subscription payment";
    }

    public static function badgeLabel(User $user): string
    {
        if (self::hasActivePayment($user)) {
            return 'Active';
        }

        if (self::isExpired($user)) {
            return 'Expired';
        }

        return 'Unpaid';
    }

    public static function badgeColor(User $user): string
    {
        if (self::hasActivePayment($user)) {
            return 'success';
        }

        if (self::isExpired($user)) {
            return 'danger';
        }

        return 'warning';
    }
}
