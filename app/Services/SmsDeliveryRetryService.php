<?php

namespace App\Services;

use App\Models\SmsDeliveryQueue;
use Illuminate\Support\Facades\Log;

class SmsDeliveryRetryService
{
    /**
     * @return array{processed: int, sent: int, still_pending: int, skipped: int}
     */
    public function retryPending(int $limit = 50): array
    {
        $records = SmsDeliveryQueue::query()
            ->where('status', 'pending')
            ->orderBy('id')
            ->limit($limit)
            ->get();

        return $this->retryCollection($records);
    }

    /**
     * @return array{success: bool, sent: int, failed: int, message: string}
     */
    public function retryById(int $queueId): array
    {
        $queue = SmsDeliveryQueue::find($queueId);
        if (! $queue) {
            return [
                'success' => false,
                'sent'    => 0,
                'failed'  => 0,
                'message' => 'Queue record not found.',
            ];
        }

        return $this->retryRecord($queue);
    }

    /**
     * @return array{success: bool, sent: int, failed: int, message: string}
     */
    public function retryRecord(SmsDeliveryQueue $queue): array
    {
        if ($queue->status !== 'pending') {
            return [
                'success' => false,
                'sent'    => 0,
                'failed'  => 0,
                'message' => 'Only pending queue rows can be retried.',
            ];
        }

        $contacts = $queue->contacts ?? [];
        if ($contacts === []) {
            $queue->update(['status' => 'sent', 'failed_count' => 0]);

            return [
                'success' => true,
                'sent'    => 0,
                'failed'  => 0,
                'message' => 'Queue row had no contacts; marked as sent.',
            ];
        }

        Log::info('Retrying Zamtel SMS delivery queue row', [
            'queue_id'     => $queue->id,
            'company_id'   => $queue->company_id,
            'sender_id'    => $queue->sender_id,
            'failed_count' => $queue->failed_count,
        ]);

        $result = SmsDispatcher::deliverViaZamtel(
            (string) $queue->company_id,
            $contacts,
            $queue->message,
            $queue->sender_id,
            $queue,
        );

        $sent   = (int) ($result['sentCount'] ?? 0);
        $failed = (int) ($result['failedCount'] ?? 0);

        return [
            'success' => $sent > 0 || $failed > 0,
            'sent'    => $sent,
            'failed'  => $failed,
            'message' => (string) ($result['responseText'] ?? 'Retry finished.'),
        ];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, SmsDeliveryQueue>|\Illuminate\Database\Eloquent\Collection<int, SmsDeliveryQueue>  $records
     * @return array{processed: int, sent: int, still_pending: int, skipped: int}
     */
    private function retryCollection($records): array
    {
        $processed    = 0;
        $sentTotal    = 0;
        $stillPending = 0;
        $skipped      = 0;

        foreach ($records as $queue) {
            $outcome = $this->retryRecord($queue);
            $processed++;

            if (! $outcome['success']) {
                $skipped++;
                continue;
            }

            $sentTotal += $outcome['sent'];
            $queue->refresh();
            if ($queue->status === 'pending') {
                $stillPending++;
            }
        }

        return [
            'processed'    => $processed,
            'sent'         => $sentTotal,
            'still_pending'=> $stillPending,
            'skipped'      => $skipped,
        ];
    }
}
