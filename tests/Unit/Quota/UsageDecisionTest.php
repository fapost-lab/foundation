<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Quota;

use Fapost\Foundation\Quota\DTO\UsageDecision;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class UsageDecisionTest extends TestCase
{
    public function test_allowed_without_a_limit(): void
    {
        $decision = UsageDecision::allowed();

        $this->assertTrue($decision->allowed);
        $this->assertNull($decision->limit);
        $this->assertNull($decision->used);
        $this->assertNull($decision->message);
    }

    public function test_allowed_carries_the_limit_and_used(): void
    {
        $decision = UsageDecision::allowed(100, 40);

        $this->assertTrue($decision->allowed);
        $this->assertSame(100, $decision->limit);
        $this->assertSame(40, $decision->used);
    }

    public function test_refused_carries_the_reason(): void
    {
        $decision = UsageDecision::refused(100, 100, 'Monthly active contacts limit reached.');

        $this->assertFalse($decision->allowed);
        $this->assertSame(100, $decision->limit);
        $this->assertSame(100, $decision->used);
        $this->assertSame('Monthly active contacts limit reached.', $decision->message);
    }

    public function test_allowed_rejects_negative_numbers(): void
    {
        $this->expectException(InvalidArgumentException::class);

        UsageDecision::allowed(null, -1);
    }

    public function test_refused_rejects_negative_numbers(): void
    {
        $this->expectException(InvalidArgumentException::class);

        UsageDecision::refused(-1, 0);
    }
}
