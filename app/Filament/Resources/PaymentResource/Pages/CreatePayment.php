<?php

namespace App\Filament\Resources\PaymentResource\Pages;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use App\Filament\Resources\PaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Payment;
use Http;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        
      $currentTimestamp = Carbon::now()->toIso8601String();
      $uuid = Uuid::uuid4()->toString();  
      $companyId = auth()->user()->user_id;
      $customerWallet = $data['customer_wallet']; 
      $phonePrefix = substr($customerWallet, 0, 5); 
      $airtelPrefixes = ['097', '077'];
      $mtnPrefixes = ['096', '076'];
      $zamtelPrefixes = ['095', '075'];
     
      $timeout = '60';
      if($data['amount'] == 5){
        $numberOfSms =  1000;
      }
      elseif($data['amount'] == 650){
        $numberOfSms =  2000; 
      }
      elseif($data['amount'] == 1000){
        $numberOfSms =  3000; 
      }
      elseif($data['amount'] == 1350){
        $numberOfSms =  4000; 
      }
      elseif($data['amount'] == 1450){
        $numberOfSms =  5000; 
      }
      elseif($data['amount'] == 1750){
        $numberOfSms =  6000; 
      }
      elseif($data['amount'] == 2000){
        $numberOfSms =  7000; 
      }
      else{
        $numberOfSms =  8000;  
      }


      



if($data['operator'] == 'AIRTEL'){
    $correspondent = 'AIRTEL_OAPI_ZMB';
    if (!in_array($phonePrefix, $airtelPrefixes)) {

    Notification::make()
                ->title('Invalid Phone Number')
                ->body('Please enter the valid Airtel phone number')
                ->warning()
                ->persistent()
                ->send();
                $this->halt(); 
}


}
elseif($data['operator'] == 'MTN'){
    $correspondent = 'MTN_MOMO_ZMB';  
    if (!in_array($phonePrefix, $mtnPrefixes)) {

        Notification::make()
                    ->title('Invalid Phone Number')
                    ->body('Please enter the valid Mtn phone number')
                    ->warning()
                    ->persistent()
                    ->send();
                    $this->halt(); 
    }
}
else{
    $correspondent = 'ZAMTEL_ZMB'; 
    if (!in_array($phonePrefix, $zamtelPrefixes)) {

        Notification::make()
                    ->title('Invalid Phone Number')
                    ->body('Please enter the valid Zamtel phone number')
                    ->warning()
                    ->persistent()
                    ->send();
                    $this->halt(); 
    }     
}

        
        $payload = [
            "operator" => strtolower($data['operator']),
            "phone" => $customerWallet,
            "amount" => $data['amount'],
            "reference" => $uuid,
                  ];
        
       // dd(env('PAWA_PAY_BASE_URI').'deposits');
        // Make the HTTP request
        $response = Http::withHeaders([
           
            "Authorization" => "Bearer ".env('LENCO_TOKEN'),
            "Content-Type" => "application/json",
            "accept" => "application/json",

         
        ])->timeout($timeout)->post(env('LENCO_BASE_URI').'deposits', $payload);
        

// Handle the response
$responseData = $response->json();
  
// dd($response);
// check if payment has been accepted for processing 

if ($responseData['status'] == "ACCEPTED") {
$this->getDepositStatus($uuid);

}

if ($responseData['status'] == "REJECTED") {
    $rejectionReason = $responseData['rejectionReason']['rejectionMessage'] ?? '';
   
    // The payment has been rejected
    Notification::make()
                ->title('Payment Rejected')
                ->body('The payment request has been rejected because of: '.$rejectionReason)
                ->warning()
                ->persistent()
                ->send();
                $this->halt();
    
    }

    if ($responseData['status'] == "DUPLICATE_IGNORED") {
        // The payment has been ignored
        Notification::make()
                ->title('Payment Ignored')
                ->body('The payment has been ignored as a duplicate of an already accepted payment with the same deposit ID.')
                ->warning()
                ->persistent()
                ->send();
                $this->halt();
        
        }

        else{
            //  Payment Failed
        Notification::make()
        ->title('Payment Failed')
        ->body('Whoops something went wrong! Please try again later')
        ->warning()
        ->persistent()
        ->send();
        $this->halt();
        }

    }




private function getDepositStatus(string $uuid) {



    $response = Http::withHeaders([
        "Authorization" => "Bearer ".env('PAWA_PAY_TOKEN'),
        "Content-Type" => "application/json",
     
    ])->get(env('PAWA_PAY_BASE_URI').'deposits/'.$uuid);

    $responseData = $response->json()[0];



    $maxRetries = 6;
    $retryInterval = 5;
    $foundResponse = false;
    for ($i = 0; $i < $maxRetries; $i++) {


//dd($responseData);

 if ($responseData['status'] == "COMPLETED") {
  Log::info('Payments Data: '.$responseData);     
 auth()->user()->wallet->deposit($numberOfSms, ['description' => 'Account credited with a total number of '.$numberOfSms. ' SMSes' ]);
 Payment::updateOrCreate(
['depositId' => $responseData['depositId']],
[
'company_id' => auth()->user()->user_id,
'reference' => $responseData['statementDescription'],
'merchant_reference' => $responseData['depositId'],
'customer_wallet' => $responseData['payer']['address']['value'],
'amount' => $responseData['requestedAmount'],
'fee_amount' => 0.00,
'percentage' => 0.00,
'currency' => 'ZMW',
'transaction_amount' => $responseData['requestedAmount'],
'status' => $responseData['status'] ?? null,
]


);
    
$foundResponse = true;
break;
    } 
if($responseData['status'] == "FAILED"){
    $failureReason = $responseData['failureReason']['failureMessage'] ?? '';
    Payment::updateOrCreate(
        ['depositId' => $responseData['depositId']],
        [
        'company_id' => auth()->user()->user_id,
        'reference' => $responseData['statementDescription'],
        'merchant_reference' => $responseData['depositId'],
        'customer_wallet' => $responseData['payer']['address']['value'],
        'amount' => $responseData['requestedAmount'],
        'fee_amount' => 0.00,
        'percentage' => 0.00,
        'currency' => 'ZMW',
        'transaction_amount' => $responseData['requestedAmount'],
        'status' => $responseData['status'] ?? null,
        ]
        
        
        );




    Notification::make()
        ->title('Payment Failed')
        ->body('Payment failed because of: '.$failureReason)
        ->warning()
        ->persistent()
        ->send();
        $this->halt();
       }
      
        
        
       sleep($retryInterval);
}


if($foundResponse) {
Log::info('Payments Data: '.$responseData);
Notification::make()
->title('Payment Successfull')
->body('Payment approved successfully')
->success()
->persistent()
->send();
$this->halt();
}   
   

elseif(!$foundResponse) {
    Notification::make()
    ->title('Payment Pending')
    ->body('Payment pending approval')
    ->info()
    ->persistent()
    ->send();
    $this->halt();

}


    
}
}
