<?php

namespace App\Filament\Resources\SenderIdResource\Pages;

use App\Filament\Resources\SenderIdResource;
use App\Models\SenderId;
use App\Support\SuperadminSmsTest;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSenderId extends ViewRecord
{
    protected static string $resource = SenderIdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('testSendSms')
                ->label('Send test SMS')
                ->icon('heroicon-o-paper-airplane')
                ->color('warning')
                ->visible(fn () => auth()->user()?->hasRole('super_admin') ?? false)
                ->form(fn () => SuperadminSmsTest::testSendFormSchema())
                ->action(function (array $data): void {
                    /** @var SenderId $record */
                    $record = $this->getRecord();
                    SuperadminSmsTest::notifyResult(
                        SuperadminSmsTest::send($record, $data['numbers'], $data['message'])
                    );
                }),
            Actions\EditAction::make(),
        ];
    }
}
