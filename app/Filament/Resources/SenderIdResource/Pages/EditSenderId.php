<?php

namespace App\Filament\Resources\SenderIdResource\Pages;

use App\Filament\Resources\SenderIdResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use App\Models\SenderId;
use App\Models\User;

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
