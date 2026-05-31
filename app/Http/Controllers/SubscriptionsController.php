<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;


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
        $paymentData = json_decode($request->input('data'), true);
        $reference = $paymentData['reference'] ?? null;

     Log::info('Subscription payment completed', $request->all());

        // Map amount to SMS bundle
        $bundles = [
             
            500  => 1000,
            800  => 2000,
            1100 => 3000,
            1400 => 4000,
            1700 => 5000,
            2000 => 6000,
            2200 => 7000,
            2500 => 8000,
            3000 => 10000,
        ];

        $numberOfSms = $bundles[$amount] ?? 0; // fallback to 0 if invalid amount

        // Save the payment
        $payment = Payment::create([
            'company_id'         => auth()->user()->user_id,
            'reference'          => $reference,
            'currency'           => $paymentData['currency'] ?? '',
            'customer_wallet'    => $paymentData['mobileMoneyDetails']['phone'] ?? '',
            'amount'             => $paymentData['amount'] ?? '',
            'transaction_amount' => $paymentData['amount'] ?? '',
            'depositId'          => $paymentData['id'] ?? '',
            'status'             => $paymentData['status'] ?? '',
            'fee_amount'         => $paymentData['fee'] ?? '',
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

    public function completeEmailSubscription(Request $request)
    {
        try {
            $paymentData = json_decode($request->input('data'), true);

            Log::info('Email subscription payment received', $request->all());

            Payment::create([
                'company_id'         => auth()->user()->user_id,
                'reference'          => $paymentData['reference'] ?? null,
                'currency'           => $paymentData['currency'] ?? '',
                'customer_wallet'    => $paymentData['mobileMoneyDetails']['phone'] ?? '',
                'amount'             => $paymentData['amount'] ?? 300,
                'transaction_amount' => $paymentData['amount'] ?? 300,
                'depositId'          => $paymentData['id'] ?? '',
                'status'             => $paymentData['status'] ?? 'successful',
                'fee_amount'         => $paymentData['fee'] ?? '',
                'messages'           => 'Bulk Email subscription — K300/month',
            ]);

            auth()->user()->update(['email_subscribed' => true]);

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Email subscription error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function completeWhatsAppSubscription(Request $request)
    {
        try {
            $paymentData = json_decode($request->input('data'), true);

            Log::info('WhatsApp subscription payment received', $request->all());

            $payment = Payment::create([
                'company_id'         => auth()->user()->user_id,
                'reference'          => $paymentData['reference'] ?? null,
                'currency'           => $paymentData['currency'] ?? '',
                'customer_wallet'    => $paymentData['mobileMoneyDetails']['phone'] ?? '',
                'amount'             => $paymentData['amount'] ?? 500,
                'transaction_amount' => $paymentData['amount'] ?? 500,
                'depositId'          => $paymentData['id'] ?? '',
                'status'             => $paymentData['status'] ?? 'successful',
                'fee_amount'         => $paymentData['fee'] ?? '',
                'messages'           => 'WhatsApp Business subscription — K500/month',
            ]);

            auth()->user()->update(['whatsapp_subscribed' => true]);

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
}