<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Quota;

use Fapost\Foundation\Quota\Exceptions\VolumeLimitReachedException;
use PHPUnit\Framework\TestCase;

final class VolumeLimitReachedExceptionTest extends TestCase
{
    public function test_it_reads_for_people_with_numbers(): void
    {
        $e = new VolumeLimitReachedException('outbound_messages', 1000, 1000);

        $this->assertSame('outbound_messages', $e->key);
        $this->assertSame(1000, $e->limit);
        $this->assertSame(1000, $e->used);
        $this->assertSame('Limit "outbound_messages" reached: 1000 of 1000 this period.', $e->getMessage());
    }

    public function test_it_reads_without_numbers(): void
    {
        $e = new VolumeLimitReachedException('outbound_messages');

        $this->assertSame('Limit "outbound_messages" reached for this period.', $e->getMessage());
    }

    public function test_it_prefers_the_operators_message(): void
    {
        $e = new VolumeLimitReachedException('outbound_messages', 5, 5, 'Upgrade your plan.');

        $this->assertSame('Upgrade your plan.', $e->getMessage());
    }
}
