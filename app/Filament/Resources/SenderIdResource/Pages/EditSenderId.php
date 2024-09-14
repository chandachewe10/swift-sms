<?php

namespace App\Filament\Resources\SenderIdResource\Pages;

use App\Filament\Resources\SenderIdResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use App\Models\SenderId;
use App\Models\User;
use Http;

class EditSenderId extends EditRecord
{
    protected static string $resource = SenderIdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }


    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->update($data);

$status = '';
if($data['is_approved'] == 1){
$status = 'APPROVED';


 $message = "Congratulations! Your senderID has been approved, you are now ready to use our BulkSMS System. All the best";
 $encodedContacts = urlencode($data['company_phone']);
 $encodedSenderId = 'MACROIT';
 $encodedMessage = urlencode($message);
 
 $url = env('BULK_SMS_BASE_URI') . '/api_key/' . urlencode(env('BULK_SMS_TOKEN')) . '/contacts/' . $encodedContacts . '/senderId/' . $encodedSenderId . '/message/' . $encodedMessage;
 
 
 $response = Http::timeout(300)->get($url);
 $responseData = $response->json();

}
elseif(data['is_approved'] == 2){
 $status = 'PENDING APPROVAL';
}
elseif(data['is_approved'] == 3){
$status = 'REJECTED';
}
else{
    $status = 'INVALID STATUS';
}
        Notification::make()
            ->title($status)
            ->body('Executed Successfully. Status: '.$status)
            ->info()
            ->persistent()
            ->send();
            $this->halt();
            return $data;
    }

    
}
