<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\SuccessfullSms;
use App\Models\UnSuccessfullSms;
use App\Models\User;
use App\Models\SenderId;
use App\Models\Messages;
use Illuminate\Support\Facades\Http;
use App\Models\Payment;
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
             $senderId = $request->sender_id;   
             $message = $request->message;
             $user = SenderId::where('name',"=",$senderId)->where('is_approved',"=",1)->first();
             $numbersArray = explode(',', $request->numbers); 
             $count = count($numbersArray);
             
      // Check if the sender Id is valid
             if(is_null($user)) {
                
                return response()->json(['success'=>'false','message' => 'Invalid Sender ID. Please send us an email at info@swift-sms.net'], 422);
            } 
      
      
             // Check if the SMS balance is less than the message request and terminate the request
             $company = User::where('user_id',"=",$user->company_id)->first();
             if($company->wallet->balance  < $count) {
                $difference = ($count) - ($company->wallet->balance); 
                return response()->json(['success'=>'false','message' => 'Insufficient SMS Balance of: '.$difference. ' to send all the message(s)'], 422);
            } 



              $url = env('BULK_SMS_BASE_URI') . '/api_key/' . urlencode(env('BULK_SMS_TOKEN')) . '/contacts/' . urlencode($contacts) . '/senderId/' . urlencode($senderId) . '/message/' . urlencode($message);
              $response = Http::get($url);

             $data = $response->collect();   
             if ($response->status() == 200)  {
              
                $company->wallet->withdraw(count(explode(',',$request->numbers)),['description' => 'Sending of SMS(s) via APIs']);
                Messages::create([
                    'message' => $message,
                    'responseText' => $data['responseText'],
                    'contact' => $contacts,
                    'status' => $response->status(),
                    'company_id' => $company->user_id,
                    ]);



                return response()->json(['success'=>'true','message' => $data['responseText']], 202);
             } 
             
             else  {
                Messages::create([
                    'message' => $message,
                    'responseText' => $data['responseText'],
                    'contact' => $contacts,
                    'status' => $response->status(),
                    'company_id' => $company->user_id,
                    ]);


                    
                return response()->json(['success'=>'false','message' => $data['responseText']], $response->status());
             } 


                         
                         
             
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

        
        $uuid = $data['depositId'] ?? '';
        $status = $data['status'] ?? '';
        
       
        $paymentsUpdate = Payment::updateOrCreate(
            ['depositId' => $uuid],
            [
                'status' => $status
            ]
        );

        // Find the user based on the company_id
        $user = User::where('user_id', $paymentsUpdate->company_id)->first();

        if ($user && $status === "COMPLETED") {
            $numberOfSms = $paymentsUpdate->messages ?? 0;
            if ($numberOfSms > 0) {
                $user->wallet->deposit($numberOfSms, [
                    'description' => 'Account credited with a total of ' . $numberOfSms . ' SMSes'
                ]);
            }
        }

       echo 'OK'; // I have recieved the payload
    } catch (\Exception $e) {
        Log::error("Payment Response Error: " . $e->getMessage());
        return response()->json(['error' => 'Failed to process payment'], 500);
    }
}

}