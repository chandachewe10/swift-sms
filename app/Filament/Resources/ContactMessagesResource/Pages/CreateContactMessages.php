<?php

namespace App\Filament\Resources\ContactMessagesResource\Pages;

use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\ContactMessagesResource;
use Filament\Actions;
use App\Models\Contact;
use App\Models\Messages; 
use Filament\Resources\Pages\CreateRecord;
use Http;
use Filament\Notifications\Notification; 

class CreateContactMessages extends CreateRecord
{
    protected static string $resource = ContactMessagesResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // Fetch contacts using findMany
        $ids = $data['contact']; // This should be an array of IDs
        $contacts = Contact::findMany($ids);
        
        // Extract phone numbers or other contact details
        $contactStrings = $contacts->map(function($contact) {
            return $contact->phone1; 
        })->toArray();
        
        // Get the sender ID and message
        $senderId = auth()->user()->sender_id;
        $message = $data['message'];
        
        // Check user balance
        $user = auth()->user();
        $balance = $user->wallet->balance;
        
        if ($balance > count($contactStrings)) {
            $difference = count($contactStrings) - $balance;
            Notification::make()
                ->title('Insufficient SMS Balance')
                ->body('You have insufficient SMS balance to send ' . $difference . ' message(s).')
                ->warning()
                ->send();
            $this->halt(); // Stop further execution
        }
        
        // Convert the array of contact strings into a comma-separated string
        $contactsString = implode(',', $contactStrings);
        
        // URL encode the components
        $encodedContacts = urlencode($contactsString);
        $encodedSenderId = urlencode($senderId);
        $encodedMessage = urlencode($message);
        
        // Construct the URL with properly encoded components
        $url = env('BULK_SMS_BASE_URI') . '/api_key/' . urlencode(env('BULK_SMS_TOKEN')) . '/contacts/' . $encodedContacts . '/senderId/' . $encodedSenderId . '/message/' . $encodedMessage;
        
        // Send the HTTP request
        $response = Http::get($url);
        
        // Handle the response
        $responseData = $response->json();
       
        // Prepare data for message record creation
        $messageData = [
            'message' => $message,
            'responseText' => $responseData['responseText'] ?? '',
            'contact' => $contactsString,
            'status' => $response->status(),
            'company_id' => auth()->user()->user_id
        ];
        
        // Create the message record
        $data = Messages::create($messageData);
        
        // Send notification based on response status
        if ($responseData['statusCode'] == 202) {
            // Withdraw the amount from the user's wallet
           // $user->wallet->withdraw(count($contactStrings), ['description' => 'Sending SMS']);
            
            Notification::make()
                ->title('Message(s) Sent')
                ->body($responseData['responseText'] ?? 'SMS(es) have been queued for delivery.')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Failed to Send Message(s)')
                ->body($responseData['responseText'] ?? 'There was an error sending the SMS(es).')
                ->danger()
                ->send();
        }
        
        $this->halt(); // Stop further execution
        return $data;
    }
}
