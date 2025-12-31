<?php

namespace App\Filament\Resources\TokenResource\Pages;

use Filament\Pages\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use App\Filament\Resources\TokenResource;

class ManageTokens extends ManageRecords
{
    protected static string $resource = TokenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->createAnother(false)
                ->using(function (array $data): PersonalAccessToken {
                    $user = Auth::user();

                    // Create the token
                    $token = $user->createToken($data['name']);

                    // Fetch the newly created token
                    $personalAccessToken = PersonalAccessToken::where('tokenable_id', $user->id)
                        ->where('name', $data['name'])
                        ->latest()
                        ->first();

                    if ($personalAccessToken) {
                        Notification::make()
                            ->success()
                            ->title('Your API key has been created successfully. Make sure to copy and store it securely, as it will not be shown again')
                            ->body($token->plainTextToken)
                            ->persistent()
                            ->send();

                        return $personalAccessToken;
                    }

                    throw new \Exception('Token creation failed');
                }),
        ];
    }
}
