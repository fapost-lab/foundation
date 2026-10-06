<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Quota;

use Fapost\Foundation\Quota\Exceptions\RecordLimitReachedException;
use PHPUnit\Framework\TestCase;

final class RecordLimitReachedExceptionTest extends TestCase
{
    public function test_it_carries_the_limit_and_reads_for_people(): void
    {
        $e = new RecordLimitReachedException('assistants', 'Assistants', 1, 1);

        $this->assertSame('assistants', $e->key);
        $this->assertSame('Assistants', $e->label);
        $this->assertSame(1, $e->limit);
        $this->assertSame(1, $e->current);
        $this->assertSame('Assistants limit reached: 1 of 1.', $e->getMessage());
    }
}
