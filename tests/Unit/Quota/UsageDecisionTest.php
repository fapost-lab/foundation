<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Quota;

use DateTimeImmutable;
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

    public function test_refused_carries_the_end_of_the_period_when_given(): void
    {
        $end = new DateTimeImmutable('2026-11-01T00:00:00+00:00');

        $this->assertSame($end, UsageDecision::refused(100, 100, null, $end)->periodEndsAt);
    }

    public function test_the_period_end_is_absent_by_default(): void
    {
        $this->assertNull(UsageDecision::refused(100, 100)->periodEndsAt);
        $this->assertNull(UsageDecision::allowed(100, 40)->periodEndsAt);
    }

    public function test_a_plain_decision_is_not_approaching(): void
    {
        $this->assertFalse(UsageDecision::allowed(100, 40)->approaching);
        $this->assertFalse(UsageDecision::refused(100, 100)->approaching);
    }

    public function test_allowed_carries_the_end_of_the_period_when_given(): void
    {
        $end = new DateTimeImmutable('2026-11-01T00:00:00+00:00');

        $this->assertSame($end, UsageDecision::allowed(100, 40, $end)->periodEndsAt);
    }

    public function test_approaching_is_an_allowed_decision_with_the_flag_and_the_period(): void
    {
        $end      = new DateTimeImmutable('2026-11-01T00:00:00+00:00');
        $decision = UsageDecision::approaching(100, 80, $end);

        $this->assertTrue($decision->allowed);
        $this->assertTrue($decision->approaching);
        $this->assertSame(100, $decision->limit);
        $this->assertSame(80, $decision->used);
        $this->assertNull($decision->message);
        $this->assertSame($end, $decision->periodEndsAt);
    }

    public function test_approaching_allows_the_period_to_be_omitted_and_used_to_pass_the_limit(): void
    {
        $decision = UsageDecision::approaching(100, 101);

        $this->assertNull($decision->periodEndsAt);
        $this->assertSame(101, $decision->used);
    }

    public function test_approaching_rejects_a_limit_below_one(): void
    {
        $this->expectException(InvalidArgumentException::class);

        UsageDecision::approaching(0, 0);
    }

    public function test_approaching_rejects_a_negative_used_count(): void
    {
        $this->expectException(InvalidArgumentException::class);

        UsageDecision::approaching(100, -1);
    }
}
