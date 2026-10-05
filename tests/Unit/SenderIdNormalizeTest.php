<?php

namespace Tests\Unit;

use App\Models\SenderId;
use PHPUnit\Framework\TestCase;

class SenderIdNormalizeTest extends TestCase
{
    public function test_normalize_name_trims_and_removes_internal_spaces(): void
    {
        $this->assertSame('MACRO-IT', SenderId::normalizeName('  MACRO IT  '));
        $this->assertSame('SWIFT', SenderId::normalizeName("SWIFT\n"));
        $this->assertNull(SenderId::normalizeName('   '));
        $this->assertNull(SenderId::normalizeName(null));
    }
}
