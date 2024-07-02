<?php

namespace App\Filament\Resources\MessagesResource\Pages;

use App\Filament\Resources\MessagesResource;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use App\Models\Messages;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Carbon\Carbon;
use Http;

class CreateMessages extends CreateRecord
{
    protected static string $resource = MessagesResource::class;


    protected function handleRecordCreation(array $data): Model
    {
        
        $contacts = $data['contact']; 
        $senderId = auth()->user()->senderId;   
        $message = $data['message'];
        
            if(auth()->user()->wallet->balance  < count($contacts)) {
                $difference = (count($contacts)) - (auth()->user()->wallet->balance); 
                Notification::make()
            ->title('Insufficient SMS Balance')
            ->body('You have Insufficient SMS Balance to send ' .$difference. ' number of Message(s)')
            ->warning()
            ->send();
               
            } 


            $url = env('BULK_SMS_BASE_URI') . '/api/v2.1/action/send/api_key/' . urlencode(env('BULK_SMS_TOKEN')) . '/contacts/' . urlencode($contacts) . '/senderId/' . urlencode($senderId) . '/message/' . urlencode($message);

            
            $response = Http::get($url);


            $response = $response->collect();   
            if ($response->status() == 200)  {
               
               auth()->user()->wallet->withdraw(count($contacts),['description' => 'Sending of SMS(s)']);

               Messages::create([
               'message' => $message,
               'responseText' => $response['responseText'],
               'contact' => $contacts,
               'status' => $response->status(),
               'user_id' => auth()->user()->id,
               ]);

               toast('Message(s) Sent Successfully!','success');
               return redirect()->back();   
            } 

            elseif ($response->status() == 422)  {
                UnSuccessfullSms::create([
                'message' => $message,
                'responseText' => $data['responseText'],
                'contact' => $contacts,
                'status' => $response->status(),
                'user_id' => auth()->user()->id,
                ]);

             toast('Message(s) Not sent!','warning');
             return redirect()->back();   
             } 


             else{
                UnSuccessfullSms::create([
                    'message' => $message,
                    'responseText' => $data['responseText'],
                    'contact' => $contacts,
                    'status' => $response->status(),
                    'user_id' => auth()->user()->id,
                    ]); 

             toast('Message(s) Not sent!','warning');
             return redirect()->back();   
             }
            
        // $messages = Messages::create([
        // 'message' => 12    
        // ]);
        // return $messages; 


    }
}
