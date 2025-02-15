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
    
      
        
      $currentTimestamp = Carbon::now()->toIso8601String();
      $uuid = Uuid::uuid4()->toString();  
      $companyId = auth()->user()->user_id;
      $returnUrl = 'https://swift-sms.net/admin';
      $apiBaseUri = env('PAWAPAY_BASE_URI');
      $apiEndpoint = '/widget/sessions';
      $apiToken = env('PAWAPAY_TOKEN');
      $phone = '26'.$data['customer_wallet'];
      $timeout = 30;



 // Request Payload
    $payload = [
   'depositId' => $uuid,
   'returnUrl' => $returnUrl ,
   'amount' => $data['amount'], 
   "statementDescription"=> "Payments of $numberOfSms SMSes",
   "msisdn" => $phone,
   "language" => "EN",
   "country" => "ZMB",
   "reason" => "Payments of SMSes",
  ];

try {

  $response = Http::withToken($apiToken)
      ->withHeaders(['Content-Type' => 'application/json'])
      ->timeout($timeout)->post($apiBaseUri.$apiEndpoint, $payload);


  if ($response->successful()) {

    Payment::updateOrCreate(
      ['depositId' => $uuid],
      [
      'company_id' => auth()->user()->user_id,
      'reference' => 'SMSes TopUp',
      'merchant_reference' => '',
      'customer_wallet' => $phone,
      'amount' => $data['amount'],
      'currency' => 'ZMW',
      'transaction_amount' => $data['amount'],
      'messages' => $numberOfSms,
      'status' => 'SUBMITTED',

      ]
    );
        

$redirectUrl = $response->json('redirectUrl');


      return redirect()->away($redirectUrl);
  } else {
    Notification::make()
                ->title('PAYMENT FAILED')
                ->body('Failed to create payment. Please try again.')
                ->warning()
                ->persistent()
                ->send();
                $this->halt(); 
      
  }
} catch (\Exception $e) {
  Notification::make()
  ->title('PAYMENT FAILED')
  ->body('Failed to create payment due to: ' . $e->getMessage())
  ->warning()
  ->persistent()
  ->send();
  $this->halt();
  
}
}




}




