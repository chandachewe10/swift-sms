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

      $smsMap = [
        500 => 1000,
        800 => 2000,
        1100 => 3000,
        1400 => 4000,
        1700 => 5000,
        2000 => 6000,
        2200 => 7000,
        2500 => 8000
    ];
    $numberOfSms = $smsMap[$data['amount']];
    $uuid = Uuid::uuid4()->toString();
    $lencoEndpoint = "collections/mobile-money";
   
    try {
     
      $response = Http::withHeaders([
          'Accept' => 'application/json',
          'Content-Type' => 'application/json',
          'Authorization' => env('LENCO_SECRET_KEY'), 
      ])->post(env('LENCO_BASE_URI').'/'.$lencoEndpoint, [ 
          'operator'  => strtolower($data['operator']),
          'phone'     => $data['customer_wallet'],
          'amount'    => $data['amount'],
          'reference' =>  $uuid
      ]);



$response = $response->json();


      
      if ($response['status'] == true &&  $response['data']['status'] == 'pay-offline') {

       

        $payment = Payment::create([
         
          'depositId' => $uuid,
          'company_id' => auth()->user()->user_id,
          'reference' => $numberOfSms .' SMSes TopUp',
          'merchant_reference' => '',
          'customer_wallet' => $data['customer_wallet'],
          'amount' => $data['amount'],
          'currency' => 'ZMW',
          'transaction_amount' => $data['amount'],
          'messages' => $numberOfSms,
          'status' => $response['data']['status'],
    
          
        ]);

        
        throw new \Exception("Please approve the payment request on your phone to complete the transaction.");
       
        
    
      } else {
        throw new \Exception('Payment Failed. Message: '.$response['message']);
        
       
      }
  } catch (\Exception $e) {
    Notification::make()
    ->title('Payment Update')
    ->body('Payment Update: '.$e->getMessage())
    ->info()
    ->persistent()
    ->send();
    $this->halt();

  }
      
        
     

return $data;
    
 
    }


}




