<?php

namespace App\Filament\Resources\ContactMessagesResource\Pages;

use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\ContactMessagesResource;
use Filament\Actions;
use App\Models\Contact;
use App\Models\Messages; 
use App\Models\SenderId;
use Filament\Resources\Pages\CreateRecord;
use Http;
use Filament\Notifications\Notification; 

class CreateContactMessages extends CreateRecord
{
    protected static string $resource = ContactMessagesResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // Check if sending to all contacts
        if ($data['send_to_all'] ?? false) {
            // Get all contacts for the company
            $contacts = Contact::where('company_id', auth()->user()->user_id)->get();
            
            if ($contacts->isEmpty()) {
                Notification::make()
                    ->title('No Contacts Found')
                    ->body('You have no contacts to send messages to.')
                    ->warning()
                    ->send();
                $this->halt();
            }
        } else {
            // Use selected contacts
            $ids = $data['contact'] ?? [];
            
            if (empty($ids)) {
                Notification::make()
                    ->title('No Contacts Selected')
                    ->body('Please select contacts or choose "Send to All Contacts".')
                    ->warning()
                    ->send();
                $this->halt();
            }
            
            $contacts = Contact::findMany($ids);
        }
        
        // Extract phone numbers from contacts
        $contactStrings = $contacts->map(function($contact) {
            return $contact->phone1; 
        })->filter()->toArray(); // Filter out empty phone numbers
        
        if (empty($contactStrings)) {
            Notification::make()
                ->title('No Valid Phone Numbers')
                ->body('None of the selected contacts have valid phone numbers.')
                ->warning()
                ->send();
            $this->halt();
        }
        
        // Get the sender ID and message
        $senderId = SenderId::where('company_id', "=", auth()->user()->user_id)
            ->where('is_approved', '=', 1)
            ->first()->name ?? '';
        $message = $data['message'];
       
        if (is_null($senderId) || empty($senderId)) {
            Notification::make()
                ->title('Invalid SenderId')
                ->body('Please wait for your Sender ID to be approved')
                ->warning()
                ->send();
            $this->halt();
        }

        // Check user balance
        $user = auth()->user();
        $balance = $user->wallet->balance;
        $contactCount = count($contactStrings);
        
        if ($balance < $contactCount) {
            $difference = $contactCount - $balance;
            Notification::make()
                ->title('Insufficient SMS Balance')
                ->body("You have insufficient SMS balance. You need {$difference} more SMS credits to send to {$contactCount} contacts.")
                ->warning()
                ->send();
            $this->halt();
        }
        
        // Process contacts in chunks of 50
        $batchSize = 50;
        $contactBatches = array_chunk($contactStrings, $batchSize);
        $allResponses = [];
        $successCount = 0;
        $failureCount = 0;
        $totalBatches = count($contactBatches);
        
        foreach ($contactBatches as $batchIndex => $batch) {
            $batchContactsString = implode(',', $batch);
            
            // URL encode the components
            $encodedContacts = urlencode($batchContactsString);
            $encodedSenderId = urlencode($senderId);
            $encodedMessage = urlencode($message);
            
            // Construct the URL with properly encoded components
            $url = env('BULK_SMS_BASE_URI') . '/api_key/' . urlencode(env('BULK_SMS_TOKEN')) . '/contacts/' . $encodedContacts . '/senderId/' . $encodedSenderId . '/message/' . $encodedMessage;
            
            try {
                // Send the HTTP request for this batch
                $response = Http::timeout(300)->get($url);
                
                // Handle the response
                $responseData = null;
                try {
                    $responseData = $response->json();
                } catch (\Exception $e) {
                    \Log::error('Failed to parse JSON response for batch', [
                        'batch' => $batchIndex + 1,
                        'status' => $response->status(),
                        'body' => $response->body(),
                        'error' => $e->getMessage()
                    ]);
                }
                
                $allResponses[] = [
                    'batch' => $batchIndex + 1,
                    'contacts_count' => count($batch),
                    'response' => $responseData,
                    'status' => $response->status()
                ];
                
                // Check if this batch was successful
                if ($responseData && isset($responseData['statusCode']) && $responseData['statusCode'] == 202) {
                    $successCount += count($batch);
                } else {
                    $failureCount += count($batch);
                    \Log::warning('SMS batch failed', [
                        'batch' => $batchIndex + 1,
                        'status' => $response->status(),
                        'response' => $responseData
                    ]);
                }
                
            } catch (\Exception $e) {
                \Log::error('SMS API Batch Request Failed', [
                    'batch' => $batchIndex + 1,
                    'error' => $e->getMessage(),
                    'batch_size' => count($batch)
                ]);
                $failureCount += count($batch);
                
                $allResponses[] = [
                    'batch' => $batchIndex + 1,
                    'contacts_count' => count($batch),
                    'response' => null,
                    'error' => $e->getMessage()
                ];
            }
            
            // Add a small delay between batches to avoid overwhelming the API
            if ($batchIndex < $totalBatches - 1) {
                usleep(500000); // 0.5 second delay between batches
            }
        }
        
        // Create consolidated response message
        $consolidatedResponseText = "Processed {$contactCount} contacts in {$totalBatches} batches. Success: {$successCount}, Failed: {$failureCount}";
        
        // Get the first successful response text or create a default one
        $firstSuccessfulResponse = collect($allResponses)->first(function($response) {
            return $response['response'] && isset($response['response']['statusCode']) && $response['response']['statusCode'] == 202;
        });
        
        if ($firstSuccessfulResponse && isset($firstSuccessfulResponse['response']['responseText'])) {
            $consolidatedResponseText = $firstSuccessfulResponse['response']['responseText'] . " | " . $consolidatedResponseText;
        }
        
        // Prepare data for message record creation
        $messageData = [
            'message' => $message,
            'responseText' => $consolidatedResponseText,
            'contact' => $data['send_to_all'] ?? false ? 'All Contacts (' . $contactCount . ')' : implode(',', $contactStrings),
            'status' => $successCount > 0 ? 200 : 400, // 200 if any succeeded, 400 if all failed
            'company_id' => auth()->user()->user_id
        ];
        
        // Create the message record
        $messageRecord = Messages::create($messageData);
        
        // Send notification based on overall results
        if ($successCount > 0) {
            // Withdraw the amount from the user's wallet (only for successful messages)
            $user->wallet->withdraw($successCount, ['description' => 'Sending SMS']);
            
            $recipientText = $data['send_to_all'] ?? false ? 'all contacts' : $contactCount . ' selected contacts';
            
            if ($failureCount > 0) {
                // Partial success
                Notification::make()
                    ->title('Messages Partially Sent')
                    ->body("Successfully sent to {$successCount} contacts, {$failureCount} failed. Processed in {$totalBatches} batches.")
                    ->warning()
                    ->send();
            } else {
                // Complete success
                Notification::make()
                    ->title('All Messages Sent Successfully')
                    ->body("SMS(es) have been queued for delivery to {$recipientText}. Processed in {$totalBatches} batches.")
                    ->success()
                    ->send();
            }
        } else {
            // Complete failure
            Notification::make()
                ->title('Failed to Send Messages')
                ->body("All {$contactCount} messages failed to send. Please check your configuration and try again.")
                ->danger()
                ->send();
        }
        
        // Log the batch processing results for debugging
        \Log::info('SMS Batch Processing Complete', [
            'total_contacts' => $contactCount,
            'total_batches' => $totalBatches,
            'success_count' => $successCount,
            'failure_count' => $failureCount,
            'user_id' => auth()->user()->user_id
        ]);
        
        $this->halt();
        return $messageRecord;
    }
}