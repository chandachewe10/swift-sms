<?php

namespace Tests\Unit;

use App\Services\SmsSendSettlement;
use PHPUnit\Framework\TestCase;

class SmsSendSettlementTest extends TestCase
{
    public function test_should_debit_local_when_attempts_were_sent_or_queued(): void
    {
        $this->assertTrue(SmsSendSettlement::shouldDebitLocal(700, 200, 500));
        $this->assertTrue(SmsSendSettlement::shouldDebitLocal(700, 0, 700));
        $this->assertTrue(SmsSendSettlement::shouldDebitLocal(1, 1, 0));
    }

    public function test_should_not_debit_local_when_nothing_sent_or_queued(): void
    {
        $this->assertFalse(SmsSendSettlement::shouldDebitLocal(10, 0, 0));
        $this->assertFalse(SmsSendSettlement::shouldDebitLocal(0, 0, 0));
    }
}
