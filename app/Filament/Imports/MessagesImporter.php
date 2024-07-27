<?php

namespace App\Filament\Imports;
use Filament\Notifications\Notification;
use Filament\Actions\CreateAction;
use App\Models\Messages;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Notifications\Actions\Action;
use Http;

class MessagesImporter extends Importer
{
    protected static ?string $model = Messages::class;
    

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('company_id')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('message')
                ->requiredMapping()
                ->rules(['required', 'max:160']),
            ImportColumn::make('contact')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('status')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('responseText')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
        ];
    }


    protected function beforeSave(): void
    {
        // Runs before a record is saved to the database.
    }

    public function resolveRecord(): ?Messages
    {
        // return Messages::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);
    

        $contacts = sprintf('0%d', $this->data['contact']);
        $senderId = auth()->user()->sender_id;
        $message = $this->data['message'];
    
       
   
        // Check if the user has enough balance
        if (auth()->user()->wallet->balance < 1) {

            static $notificationSent = false;
            if (!$notificationSent) {
            
            Notification::make()
                ->title('Insufficient SMS Balance')
                ->body('You have Insufficient SMS Balance to send the remaining SMS(es)')
                ->warning()
                ->send();
                $notificationSent = true;
                $action = new CreateAction('Insufficient');
                $action->halt();

            // Call halt() from CreateRecord class using composition
           // return false;
            }
       
        }
    
       
    
        // URL encode the components
        $encodedContacts = urlencode($contacts );
        $encodedSenderId = urlencode($senderId);
        $encodedMessage = urlencode($message);
    
        // Construct the URL with properly encoded components
        $url = env('BULK_SMS_BASE_URI') . '/api_key/' . urlencode(env('BULK_SMS_TOKEN')) . '/contacts/' . $encodedContacts . '/senderId/' . $encodedSenderId . '/message/' . $encodedMessage;
    
        // Send the HTTP request
        $response = Http::get($url);
    
        // Handle the response
        $responseData = $response->json();
        
        if ($response->successful()) {
            // Withdraw the amount from the user's wallet
           // auth()->user()->wallet->withdraw(count($contactStrings), ['description' => 'Sending of SMS(s)']);
    
            // Create the message record
           $data = Messages::create([
                'message' => $message,
                'responseText' => $responseData['responseText'] ?? '',
                'contact' => $contacts ,
                'status' => $response->status(),
                'company_id' => auth()->user()->user_id,
            ]);




        return $data;
        }
    }
    

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your messages import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
