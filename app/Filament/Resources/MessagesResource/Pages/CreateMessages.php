<?php
namespace App\Filament\Resources\MessagesResource\Pages;

use App\Filament\Resources\MessagesResource;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use App\Models\Messages;
use Filament\Resources\Pages\CreateRecord;
use Http;

class CreateMessages extends CreateRecord
{
    protected static string $resource = MessagesResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $contacts = $data['contact'];
        $senderId = auth()->user()->sender_id;
        $message = $data['message'];
    
        // Ensure each contact is a string
        $contactStrings = array_map(function($contact) {
            // Assuming each contact is an array with a 'contact' key
            return is_array($contact) ? $contact['contact'] : $contact;
        }, $contacts);
   
        // Check if the user has enough balance
        if (auth()->user()->wallet->balance > count($contactStrings)) {
            $difference = count($contactStrings) - auth()->user()->wallet->balance;
            Notification::make()
                ->title('Insufficient SMS Balance')
                ->body('You have Insufficient SMS Balance to send ' . $difference . ' number of Message(s)')
                ->warning()
                ->send();
                $this->halt();
       
        }
    
        // Convert the array of contact strings into a comma-separated string
        $contactsString = implode(',', $contactStrings);
    
        // URL encode the components
        $encodedContacts = urlencode($contactsString);
        $encodedSenderId = urlencode($senderId);
        $encodedMessage = urlencode($message);
    
        
        $url = env('BULK_SMS_BASE_URI') . '/api_key/' . urlencode(env('BULK_SMS_TOKEN')) . '/contacts/' . $encodedContacts . '/senderId/' . $encodedSenderId . '/message/' . $encodedMessage;
    
        // Send the HTTP request
        $response = Http::timeout(300)->get($url);

    
        // Handle the response
        $responseData = $response->json();
       
        if ($responseData['statusCode'] == 202) {
            // Withdraw the amount from the user's wallet
           // auth()->user()->wallet->withdraw(count($contactStrings), ['description' => 'Sending of SMS(s)']);
    
            // Create the message record
           $data = Messages::create([
                'message' => $message,
                'responseText' => $responseData['responseText'] ?? '',
                'contact' => $contactsString,
                'status' => $response->status(),
                'company_id' => auth()->user()->user_id,
            ]);
    
            Notification::make()
                ->title('Message(s) sent')
                ->body($responseData['responseText'] ?? 'SMS(es) have been queued for delivery')
                ->success()
                ->send();
                $this->halt();
                return $data;
        } else {
            $data = Messages::create([
                'message' => $message,
                'responseText' => $responseData['responseText'] ?? 'There was an error sending the SMS(es).',
                'contact' => $contactsString,
                'status' => $response->status(),
                'company_id' => auth()->user()->user_id,
            ]);
    
            Notification::make()
                ->title('Failed to send message(s)')
                ->body($responseData['responseText'] ?? 'There was an error sending the SMS(es).')
                ->danger()
                ->send();
                $this->halt();
                return $data;
        }
    }
}    