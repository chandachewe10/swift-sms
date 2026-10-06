<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Models\Messages;
use App\Models\Payment;
use App\Models\SenderId;
use App\Models\User;
use App\Services\SmsDispatcher;
use App\Services\SmsSendSettlement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MessagesAPI extends Controller
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
        return view('messages.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {




             $contacts = $request->numbers;
             $message = $request->message;
             $user = SenderId::findApprovedByName((string) $request->sender_id);
             $numbersArray = explode(',', $request->numbers);
             $count = count($numbersArray);

      // Check if the sender Id is valid
             if(is_null($user)) {

                return response()->json(['success'=>'false','message' => 'Invalid Sender ID. Please send us an email at swiftsms@macroit.org'], 422);
            }


             $company = User::where('user_id', $user->company_id)->first();

             $numbersArray = array_filter(array_map('trim', explode(',', $contacts)));
             $split        = \App\Services\SmsDispatcher::splitByType(array_values($numbersArray));
             $localCount   = count($split['local']);
             $intlCount    = count($split['international']);

             // Check local balance
             if ($localCount > 0 && $company->wallet->balance < $localCount) {
                 $diff = $localCount - $company->wallet->balance;
                 return response()->json(['success' => 'false', 'message' => "Insufficient local SMS balance. Need {$diff} more credit(s)."], 422);
             }

             // Check international balance
             if ($intlCount > 0 && ($company->international_sms_credits ?? 0) < $intlCount) {
                 $diff = $intlCount - ($company->international_sms_credits ?? 0);
                 return response()->json(['success' => 'false', 'message' => "Insufficient international SMS balance. Need {$diff} more credit(s)."], 422);
             }

             $options = [
                 'flash'    => (bool) ($request->flash_sms   ?? false),
                 'schedule' => $request->schedule_at ?? null,
             ];

             $result = SmsDispatcher::send(
                 $company->user_id,
                 array_values($numbersArray),
                 $message,
                 $options
             );

             $settlement = SmsSendSettlement::settle($company, $result, $localCount, $intlCount);

             Messages::create([
                 'message'      => $message,
                 'responseText' => $settlement['client_message'] ?: $result['responseText'],
                 'contact'      => $contacts,
                 'status'       => $settlement['success'] ? 202 : ($result['statusCode'] ?: 500),
                 'company_id'   => $company->user_id,
             ]);

             if ($settlement['success']) {
                 return response()->json(['success' => 'true', 'message' => $settlement['client_message']], 202);
             }

             return response()->json(['success' => 'false', 'message' => "Can't send message(s) right now please try again later"], $result['statusCode'] ?: 500);





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



    public function paymentResponse(Request $request)
{
    try {
        // Get the request data
        $data = $request->all();
        Log::info("Payment Logged: ", $data);


        $uuid = $data['data']['reference'] ?? '';
        $status = $data['data']['status'] ?? '';


        $paymentsUpdate = Payment::updateOrCreate(
            ['depositId' => $uuid],
            [
                'status' => $status
            ]
        );

        // Find the user based on the company_id
        $user = User::where('user_id', $paymentsUpdate->company_id)->first();

        if ($user && $status === "successful") {
            $numberOfSms = $paymentsUpdate->messages ?? 0;
            if ($numberOfSms > 0) {
                $user->wallet->deposit($numberOfSms, [
                    'description' => 'Account credited with a total of ' . $numberOfSms . ' SMSes'
                ]);
            }
        }

       echo 200; // I have recieved the payload
    } catch (\Exception $e) {
        Log::error("Payment Response Error: " . $e->getMessage());
        return response()->json(['error' => 'Failed to process payment'], 500);
    }
}

}
