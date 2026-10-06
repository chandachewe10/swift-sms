<?php

namespace App\Console\Commands;

use App\Jobs\RetrySmsDeliveryQueueJob;
use App\Services\SmsDeliveryRetryService;
use Illuminate\Console\Command;

class RetrySmsDeliveryQueueCommand extends Command
{
    protected $signature = 'sms:retry-queue
                            {--id= : Retry a single sms_delivery_queue row by ID}
                            {--limit=50 : Max pending rows to retry when --id is omitted}
                            {--queue : Dispatch to the queue worker instead of running synchronously}';

    protected $description = 'Resend pending Zamtel SMS rows from sms_delivery_queue (no extra wallet debit)';

    public function handle(SmsDeliveryRetryService $retryService): int
    {
        $id    = $this->option('id');
        $limit = max(1, (int) $this->option('limit'));

        if ($this->option('queue')) {
            RetrySmsDeliveryQueueJob::dispatch(
                $id !== null ? (int) $id : null,
                $limit,
            );
            $this->info('Retry job dispatched to the queue.');

            return self::SUCCESS;
        }

        if ($id !== null) {
            $result = $retryService->retryById((int) $id);
            $this->line($result['message']);
            $this->table(
                ['Sent', 'Still failed / pending'],
                [[$result['sent'], $result['failed']]]
            );

            return $result['success'] ? self::SUCCESS : self::FAILURE;
        }

        $summary = $retryService->retryPending($limit);
        $this->info("Processed {$summary['processed']} row(s).");
        $this->table(
            ['Sent (messages)', 'Rows still pending', 'Rows skipped'],
            [[
                $summary['sent'],
                $summary['still_pending'],
                $summary['skipped'],
            ]]
        );

        return self::SUCCESS;
    }
}
