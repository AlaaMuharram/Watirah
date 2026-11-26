<?php

namespace Tests\Unit;

use App\Services\WatiraDatabase;
use PHPUnit\Framework\TestCase;

class WatiraDatabaseTest extends TestCase
{
    public function test_datetime_roundtrip_matches_the_previous_api(): void
    {
        $this->assertSame('2026-11-26 09:39:00.123', WatiraDatabase::toDb('2026-11-26T09:39:00.123Z'));
        $this->assertSame('2026-11-26T09:39:00.123Z', WatiraDatabase::fromDb('2026-11-26 09:39:00.123'));
        $this->assertSame('2026-11-26T09:39:00.000Z', WatiraDatabase::fromDb('2026-11-26 09:39:00'));
        $this->assertNull(WatiraDatabase::toDb(null));
        $this->assertNull(WatiraDatabase::fromDb(null));
    }
}
