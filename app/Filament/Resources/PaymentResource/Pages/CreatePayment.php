<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use App\Models\Payment;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;

    /**
     * Super-admins get the standard Create / Create & Create Another / Cancel
     * buttons. Regular users see the bundle catalog with "Buy Now" link buttons
     * instead of a traditional form submit.
     */
    protected function getFormActions(): array
    {
        if (auth()->user()?->hasRole('super_admin')) {
            return parent::getFormActions();
        }

        return [];
    }

    /**
     * Create the Payment record and, when the status is "successful", activate
     * the associated subscription or credit the appropriate balance.
     */
    protected function handleRecordCreation(array $data): Model
    {
        $subscriptionType = $data['subscription_type'] ?? null;
        $smsBundleAmount  = (int) ($data['sms_bundle']  ?? 0);
        $intlBundleAmount = (int) ($data['intl_bundle'] ?? 0);

        $localBundles = [
            340   => 1000,
            1340  => 5000,
            2000  => 9000,
            4750  => 25000,
            9000  => 50000,
            17000 => 100000,
        ];

        $intlBundles = [
            1050  => 100,
            2625  => 250,
            5250  => 500,
            10500 => 1000,
        ];

        // Auto-generate the description when none was typed.
        if (empty($data['messages'])) {
            $data['messages'] = match ($subscriptionType) {
                'whatsapp'          => 'WhatsApp Business subscription — K500/month',
                'email'             => 'Bulk Email subscription — K500/month',
                'local_sms'         => 'Payment for ' . number_format($localBundles[$smsBundleAmount] ?? 0) . ' SMSes',
                'international_sms' => 'International SMS — ' . ($intlBundles[$intlBundleAmount] ?? 0) . ' credits',
                default             => 'Manual payment entry',
            };
        }

        // Mirror amount → transaction_amount when the admin left it blank.
        if (empty($data['transaction_amount']) && ! empty($data['amount'])) {
            $data['transaction_amount'] = $data['amount'];
        }

        // Strip virtual fields — Eloquent would silently drop them, but being
        // explicit prevents any confusion with future fillable changes.
        unset($data['subscription_type'], $data['sms_bundle'], $data['intl_bundle']);

        /** @var Payment $payment */
        $payment = Payment::create($data);

        // Only activate on successful payments.
        if ($subscriptionType && $payment->status === 'successful') {
            $user = User::where('user_id', $payment->company_id)->first();

            if ($user) {
                $activated = true;

                if ($subscriptionType === 'whatsapp') {
                    $user->whatsapp_subscribed = true;
                    $user->save();

                } elseif ($subscriptionType === 'email') {
                    $user->email_subscribed = true;
                    $user->save();

                } elseif ($subscriptionType === 'local_sms') {
                    $numberOfSms = $localBundles[$smsBundleAmount] ?? 0;
                    if ($numberOfSms > 0) {
                        $user->wallet->deposit($numberOfSms, [
                            'description' => 'Manual credit — ' . number_format($numberOfSms) . ' SMSes',
                        ]);
                    } else {
                        $activated = false;
                    }
                } elseif ($subscriptionType === 'international_sms') {
                    $credits = $intlBundles[$intlBundleAmount] ?? 0;
                    if ($credits > 0) {
                        $user->increment('international_sms_credits', $credits);
                    } else {
                        $activated = false;
                    }
                } else {
                    $activated = false;
                }

                Notification::make()
                    ->title($activated
                        ? 'Payment recorded & service activated'
                        : 'Payment recorded (no service activated)')
                    ->body($activated
                        ? "The payment has been saved and the service has been activated for {$user->name}."
                        : 'The payment has been saved. No service was activated (check bundle selection).')
                    ->success()
                    ->send();

                // Suppress Filament's default "Created" notification.
                return $payment;
            }
        }

        // Default success notification when no subscription type was chosen
        // or when the user record could not be found.
        Notification::make()
            ->title('Payment recorded')
            ->body('The payment record has been saved. No subscription was activated.')
            ->success()
            ->send();

        return $payment;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        // Return null to suppress Filament's built-in notification —
        // we already sent a custom one in handleRecordCreation().
        return null;
    }
}
