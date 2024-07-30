<?php

namespace App\Filament\Resources\TokenResource\Pages;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Filament\Pages\Actions\CreateAction;
use App\Filament\Resources\TokenResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Notifications\Notification; 

class ManageTokens extends ManageRecords
{
    protected static string $resource = TokenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->createAnother(false)
            ->using(function (array $data): Model {
                $user = auth()->user();
                   
                    $tokenCreated = $user->createToken($data['remember_token'])->plainTextToken;
                    
                    if ($tokenCreated) {
                        Notification::make()
                            ->success()
                            ->title('Your API key has been created successfully. Make sure to copy and store it securely, as it will not be shown again')
                            ->body($tokenCreated)
                            ->persistent()
                            ->send();

                            $this->halt();
                    }
                }),
        ];
    }
}
