<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


class SubscriptionsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }




public function completeSubscription(Request $request, $amount)
{
    try {
        $paymentData = json_decode($request->input('data'), true) ?? [];
        $reference = $paymentData['reference'] ?? null;

        Log::info('Subscription payment completed', $request->all());

        // Map amount (ZMW) to SMS credits — Local pricing tiers
        $bundles = [
            340   => 1000,
            1340  => 5000,
            2000  => 9000,
            4750  => 25000,
            9000  => 50000,
            17000 => 100000,
        ];

        $numberOfSms = $bundles[$amount] ?? 0; // fallback to 0 if invalid amount

        // Save the payment
        $payment = Payment::create([
            'company_id'         => auth()->user()->user_id,
            'reference'          => $reference,
            'currency'           => $paymentData['currency'] ?? '',
            'customer_wallet'    => $paymentData['mobileMoneyDetails']['phone'] ?? null,
            'amount'             => $paymentData['amount'] ?? '',
            'transaction_amount' => $paymentData['amount'] ?? '',
            'depositId'          => $paymentData['id'] ?: null,
            'status'             => $paymentData['status'] ?? '',
            'fee_amount'         => isset($paymentData['fee']) ? $paymentData['fee'] : null,
            'messages'           => 'Payment for ' . $numberOfSms . ' SMSes',
        ]);

        // Credit SMS wallet
        if ($numberOfSms > 0) {
            auth()->user()->wallet->deposit($numberOfSms, [
                'description' => 'Account credited with a total of ' . $numberOfSms . ' SMSes',
            ]);
        }

        return response()->json([
            'status'  => 'success',
            'payment' => $payment,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status'  => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
}

    public function completeInternationalSubscription(Request $request, $amount)
    {
        try {
            $paymentData = json_decode($request->input('data'), true) ?? [];
            $reference   = $paymentData['reference'] ?? null;

            Log::info('International SMS subscription payment completed', $request->all());

            // Map ZMW amount to international SMS credits ($0.389/SMS @ ~K27/USD)
            $bundles = [
                1050  => 100,
                2625  => 250,
                5250  => 500,
                10500 => 1000,
            ];

            $credits = $bundles[$amount] ?? 0;

            Payment::create([
                'company_id'         => auth()->user()->user_id,
                'reference'          => $reference,
                'currency'           => $paymentData['currency'] ?? '',
                'customer_wallet'    => $paymentData['mobileMoneyDetails']['phone'] ?? null,
                'amount'             => $paymentData['amount'] ?? '',
                'transaction_amount' => $paymentData['amount'] ?? '',
                'depositId'          => $paymentData['id'] ?: null,
                'status'             => $paymentData['status'] ?? '',
                'fee_amount'         => isset($paymentData['fee']) ? $paymentData['fee'] : null,
                'messages'           => 'International SMS — ' . $credits . ' credits',
            ]);

            if ($credits > 0) {
                auth()->user()->increment('international_sms_credits', $credits);
            }

            return response()->json(['status' => 'success', 'credits' => $credits]);
        } catch (\Exception $e) {
            Log::error('International SMS subscription error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function completeEmailSubscription(Request $request)
    {
        try {
            $paymentData = json_decode($request->input('data'), true) ?? [];

            Log::info('Email subscription payment received', $request->all());

            Payment::create([
                'company_id'         => auth()->user()->user_id,
                'reference'          => $paymentData['reference'] ?? null,
                'currency'           => $paymentData['currency'] ?? '',
                'customer_wallet'    => $paymentData['mobileMoneyDetails']['phone'] ?? null,
                'amount'             => $paymentData['amount'] ?? 500,
                'transaction_amount' => $paymentData['amount'] ?? 500,
                'depositId'          => $paymentData['id'] ?: null,
                'status'             => $paymentData['status'] ?? 'successful',
                'fee_amount'         => isset($paymentData['fee']) ? $paymentData['fee'] : null,
                'messages'           => 'Bulk Email subscription — K500/month',
            ]);

            $user = auth()->user();
            $user->email_subscribed = true;
            $user->save();

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Email subscription error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function completeWhatsAppSubscription(Request $request)
    {
        try {
            $paymentData = json_decode($request->input('data'), true) ?? [];

            Log::info('WhatsApp subscription payment received', $request->all());

            $payment = Payment::create([
                'company_id'         => auth()->user()->user_id,
                'reference'          => $paymentData['reference'] ?? null,
                'currency'           => $paymentData['currency'] ?? '',
                'customer_wallet'    => $paymentData['mobileMoneyDetails']['phone'] ?? null,
                'amount'             => $paymentData['amount'] ?? 500,
                'transaction_amount' => $paymentData['amount'] ?? 500,
                'depositId'          => $paymentData['id'] ?: null,
                'status'             => $paymentData['status'] ?? 'successful',
                'fee_amount'         => isset($paymentData['fee']) ? $paymentData['fee'] : null,
                'messages'           => 'WhatsApp Business subscription — K500/month',
            ]);

            $user = auth()->user();
            $user->whatsapp_subscribed = true;
            $user->save();

            return response()->json([
                'status'  => 'success',
                'payment' => $payment,
            ]);
        } catch (\Exception $e) {
            Log::error('WhatsApp subscription error: ' . $e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Render a printable HTML receipt for a single payment.
     * Users can only view their own receipts; super_admins can view any.
     */
    public function downloadReceipt(Payment $payment): \Illuminate\View\View
    {
        $authUser = auth()->user();

        if (! $authUser->hasRole('super_admin') && $payment->company_id !== $authUser->user_id) {
            abort(403, 'You are not authorised to download this receipt.');
        }

        $customer = User::where('user_id', $payment->company_id)->first();

        return view('payments.receipt', compact('payment', 'customer'));
    }
}