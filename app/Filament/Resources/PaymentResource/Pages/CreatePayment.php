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
      $phonePrefix = substr($customerWallet, 0, 3); 
      //dd($phonePrefix);
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
    if (in_array($phonePrefix, $zamtelPrefixes)) {

        Notification::make()
                    ->title('Invalid Phone Number')
                    ->body('Please Zamtel Phone Number is not supported currently')
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
        
       
        $response = Http::withHeaders([
           
            "Authorization" => "Bearer ".env('LENCO_TOKEN'),
            "Content-Type" => "application/json",
            "accept" => "application/json",

         
        ])->timeout($timeout)->post(env('LENCO_BASE_URI').'/collections/mobile-money', $payload);
        

// Handle the response
$responseData = $response->json();
  
// dd($response);
// check if payment has been accepted for processing 

if ($responseData['status'] == true && ($responseData['data']['status'] == 'pay-offline')) {
  Notification::make()
  ->title('Approve Payment')
  ->body('Please check your mobile phone to confirm the payment request')
  ->success()
  ->persistent()
  ->send(); 

  sleep(5);

 $this->getDepositStatus($uuid);

}

if ($responseData['data']['status'] == "failed") {
    $rejectionReason = $responseData['message'] ?? '';
   
    // The payment has been rejected
    Notification::make()
                ->title('Payment Failed')
                ->body('The payment request has failed because of: '.$rejectionReason)
                ->warning()
                ->persistent()
                ->send();
                $this->halt();
    
    }

    if ($responseData['data']['status'] == "pending") {
        // The payment has been ignored
        Notification::make()
                ->title('Payment Pending')
                ->body('The payment is pending approval')
                ->info()
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

 $maxRetries = 10;
 $retryInterval = 5;
 $foundResponse = false;


 for ($i = 0; $i < $maxRetries; $i++) {


  $response = Http::withHeaders([
    "Authorization" => "Bearer ".env('LENCO_TOKEN'),
    "Content-Type" => "application/json",
 
])->get(env('LENCO_BASE_URI').'/collections/status/'.$uuid);





 if ($responseData['status'] == true && $responseData['data']['settlementStatus'] == "settled") {
  Log::info('Payments Data: '.$responseData);     
 auth()->user()->wallet->deposit($numberOfSms, ['description' => 'Account credited with a total number of '.$numberOfSms. ' SMSes' ]);

 Payment::updateOrCreate(
['depositId' => $responseData['data']['reference']],
[
'company_id' => auth()->user()->user_id,
'reference' => $responseData['data']['mobileMoneyDetails']['accountName'] ?? '',
'merchant_reference' => $responseData['data']['lencoReference'],
'customer_wallet' => $responseData['data']['mobileMoneyDetails']['phone'] ?? '',
'amount' => $responseData['data']['amount'],
'currency' => 'ZMW',
'transaction_amount' =>$responseData['data']['amount'],
'status' => $responseData['data']['settlementStatus'] ?? null,
]


);
    
$foundResponse = true;
break;


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




if ($responseData['status'] == true && $responseData['data']['settlementStatus'] == "pending") {

  $pendingReason = $responseData['data']['reasonForFailure'] ?? '';
  Payment::updateOrCreate(
    ['depositId' => $responseData['data']['reference']],
    [
    'company_id' => auth()->user()->user_id,
    'reference' => $responseData['data']['mobileMoneyDetails']['accountName'] ?? '',
    'merchant_reference' => $responseData['data']['lencoReference'],
    'customer_wallet' => $responseData['data']['mobileMoneyDetails']['phone'] ?? '',
    'amount' => $responseData['data']['amount'],
    'currency' => 'ZMW',
    'transaction_amount' =>$responseData['data']['amount'],
    'status' => $responseData['data']['settlementStatus'] ?? null,
    ]);



  Notification::make()
      ->title('Processing Payment')
      ->body('Payment Delayed because of: '.$failureReason)
      ->warning()
      ->persistent()
      ->send();
      
     }
    
      



   

else {
    Notification::make()
    ->title('Payment Update')
    ->body('Payment Failed: '.$responseData['data']['reasonForFailure'] ?? '')
    ->warning()
    ->persistent()
    ->send();
    $this->halt();

}


    
}
}
