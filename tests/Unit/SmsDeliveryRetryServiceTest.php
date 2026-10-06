<?php

namespace Tests\Unit;

use App\Models\SmsDeliveryQueue;
use App\Services\SmsDeliveryRetryService;
use PHPUnit\Framework\TestCase;

class SmsDeliveryRetryServiceTest extends TestCase
{
    public function test_retry_record_rejects_non_pending_status(): void
    {
        $queue = new SmsDeliveryQueue([
            'status' => 'sent',
            'contacts' => ['260971234567'],
        ]);

        $service = new SmsDeliveryRetryService;
        $result  = $service->retryRecord($queue);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('pending', strtolower($result['message']));
    }
}
