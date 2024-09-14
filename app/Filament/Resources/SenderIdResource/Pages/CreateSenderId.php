<?php

namespace App\Filament\Resources\SenderIdResource\Pages;

use App\Filament\Resources\SenderIdResource;
use Filament\Notifications\Notification;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use App\Models\SenderId;

class CreateSenderId extends CreateRecord
{
    protected static string $resource = SenderIdResource::class;

    protected function handleRecordCreation(array $data): Model
    {
       
        $data = SenderId::updateOrCreate(
            ['company_id' => auth()->user()->user_id],
            [
            'name' => $data['name'],
            'company_id' => auth()->user()->user_id,
            'company_phone' => $data['company_phone'],
            'is_approved' => 2
        ]);

        Notification::make()
            ->title('SenderID Pending Approval')
            ->body('SenderID has been submitted successfully and is now pending approval. You will be nortified via SMS once the approval has been done')
            ->success()
            ->persistent()
            ->send();
            $this->halt();
            return $data;
    }
}
