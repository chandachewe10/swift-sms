<?php

namespace App\Jobs;

use App\Services\SmsDeliveryRetryService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class RetrySmsDeliveryQueueJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ?int $queueId = null,
        public int $limit = 50,
    ) {}

    public function handle(SmsDeliveryRetryService $retryService): void
    {
        if ($this->queueId !== null) {
            $result = $retryService->retryById($this->queueId);
            Log::info('RetrySmsDeliveryQueueJob single row finished', [
                'queue_id' => $this->queueId,
                'result'   => $result,
            ]);

            return;
        }

        $summary = $retryService->retryPending($this->limit);
        Log::info('RetrySmsDeliveryQueueJob batch finished', $summary);
    }
}
