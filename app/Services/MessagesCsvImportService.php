<?php

namespace App\Services;

use App\Models\Messages;
use App\Models\User;

class MessagesCsvImportService
{
    /**
     * @return array{
     *     success_count: int,
     *     queued_count: int,
     *     error_count: int,
     *     insufficient_balance: bool,
     *     aborted_no_sender: bool
     * }
     */
    public function processFile(string $filePath, User $user): array
    {
        $stats = [
            'success_count'        => 0,
            'queued_count'         => 0,
            'error_count'          => 0,
            'insufficient_balance' => false,
            'aborted_no_sender'    => false,
        ];

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            $stats['error_count']++;

            return $stats;
        }

        $header = fgetcsv($handle);
        if ($header === false) {
            fclose($handle);
            $stats['error_count']++;

            return $stats;
        }

        $expectedHeaders = ['company_id', 'message', 'contact'];
        $missingHeaders  = array_diff($expectedHeaders, $header);
        if ($missingHeaders !== []) {
            fclose($handle);
            $stats['error_count']++;

            return $stats;
        }

        while (($row = fgetcsv($handle)) !== false) {
            $rowStats = $this->processRow($header, $row, $user);
            $stats['success_count'] += $rowStats['success'] ? 1 : 0;
            $stats['queued_count'] += $rowStats['queued'] ? 1 : 0;
            $stats['error_count'] += $rowStats['error'] ? 1 : 0;
            if ($rowStats['insufficient_balance']) {
                $stats['insufficient_balance'] = true;
                break;
            }
            if ($rowStats['aborted_no_sender']) {
                $stats['aborted_no_sender'] = true;
                break;
            }
        }

        fclose($handle);

        return $stats;
    }

    /**
     * @return array{success: bool, queued: bool, error: bool, insufficient_balance: bool, aborted_no_sender: bool}
     */
    public function processRow(array $header, array $row, User $user): array
    {
        $empty = [
            'success'              => false,
            'queued'               => false,
            'error'                => false,
            'insufficient_balance' => false,
            'aborted_no_sender'    => false,
        ];

        $messageData = array_combine($header, $row);
        if ($messageData === false) {
            return [...$empty, 'error' => true];
        }

        if (empty($messageData['message']) || empty($messageData['contact'])) {
            return [...$empty, 'error' => true];
        }

        if (strlen($messageData['message']) > 160) {
            return [...$empty, 'error' => true];
        }

        $numbers = [sprintf('0%d', $messageData['contact'])];
        $split   = SmsDispatcher::splitByType($numbers);
        $local   = count($split['local']);
        $intl    = count($split['international']);

        if ($local > 0 && $user->wallet->balance < $local) {
            return [...$empty, 'insufficient_balance' => true];
        }

        if ($intl > 0 && ($user->international_sms_credits ?? 0) < $intl) {
            return [...$empty, 'insufficient_balance' => true];
        }

        $message   = $messageData['message'];
        $companyId = ! empty($messageData['company_id'])
            ? $messageData['company_id']
            : $user->user_id;

        $result = SmsDispatcher::send($user->user_id, $numbers, $message, []);
        $settlement = SmsSendSettlement::settle($user, $result, $local, $intl);

        if (! $settlement['success']) {
            if (str_contains(strtolower($result['responseText'] ?? ''), 'sender id')) {
                return [...$empty, 'aborted_no_sender' => true];
            }

            return [...$empty, 'error' => true];
        }

        Messages::create([
            'message'      => $message,
            'responseText' => $settlement['client_message'] ?: $result['responseText'],
            'contact'      => $numbers[0],
            'status'       => 200,
            'company_id'   => $companyId,
        ]);

        $queued = ($result['localFailedCount'] ?? 0) > 0;

        return [
            'success'              => true,
            'queued'               => $queued,
            'error'                => false,
            'insufficient_balance' => false,
            'aborted_no_sender'    => false,
        ];
    }
}
