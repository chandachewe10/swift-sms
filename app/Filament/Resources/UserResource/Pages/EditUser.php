<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
       
        $user = \App\Models\User::updateOrCreate(
        [
            'email' => $data['email']
        ],[
            'name' => $data['name'],
            'password' =>  isset($data['password']) && $data['password'] !== ''
                ? ($data['password']) // Hash only if a new password is provided
                : $record->password,
            
        ]);

        $smsUnits = $data['units'] ?? 0;
        if ($smsUnits > 0) {
            $user->wallet->deposit($smsUnits, ['description' => 'Local SMS top-up of ' . $smsUnits . ' SMS(es) by admin']);
        }

        $intlUnits = $data['international_units'] ?? 0;
        if ($intlUnits > 0) {
            $user->increment('international_sms_credits', $intlUnits);
        }
        


return $record;
       

    }
}
