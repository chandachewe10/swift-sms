<?php

namespace App\Filament\Resources\SenderIdResource\Pages;

use App\Filament\Resources\SenderIdResource;
use Filament\Notifications\Notification;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use App\Models\SenderId;
use Http;

class CreateSenderId extends CreateRecord
{
    protected static string $resource = SenderIdResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $isSuperAdmin = auth()->user()->hasRole('super_admin');
        $companyId = ($isSuperAdmin && ! empty($data['company_id']))
            ? $data['company_id']
            : auth()->user()->user_id;
        $isApproved = ($isSuperAdmin && isset($data['is_approved']))
            ? (int) $data['is_approved']
            : 2;

        $record = SenderId::updateOrCreate(
            ['company_id' => $companyId],
            [
                'name' => $data['name'],
                'company_id' => $companyId,
                'company_phone' => $data['company_phone'],
                'is_approved' => $isApproved,
            ]
        );

        if ($isApproved === 1) {
            $message = 'Congratulations! Your senderID has been approved on SwiftSMS, you have 2 free SMS to send, top up in our portal to send More.';
            $encodedContacts = urlencode($data['company_phone']);
            $encodedSenderId = 'SWIFT';
            $encodedMessage = urlencode($message);

            $url = env('BULK_SMS_BASE_URI') . '/api_key/' . urlencode(env('BULK_SMS_TOKEN')) . '/contacts/' . $encodedContacts . '/senderId/' . $encodedSenderId . '/message/' . $encodedMessage;

            Http::timeout(300)->get($url);

            $title = 'APPROVED';
            $body = 'Sender ID created and approved for the selected business. Confirmation SMS sent.';
        } elseif ($isApproved === 3) {
            $title = 'REJECTED';
            $body = 'Sender ID record saved with rejected status for the selected business.';
        } else {
            $title = 'SenderID Pending Approval';
            $body = $isSuperAdmin
                ? 'Sender ID submitted for the selected business and is pending approval.'
                : 'SenderID has been submitted successfully and is now pending approval. You will be nortified via SMS once the approval has been done';
        }

        Notification::make()
            ->title($title)
            ->body($body)
            ->success()
            ->persistent()
            ->send();
        $this->halt();

        return $record;
    }
}
