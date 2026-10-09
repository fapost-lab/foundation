<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Quota;

use DateTimeImmutable;
use Fapost\Foundation\Quota\DTO\UsageUnit;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class UsageUnitTest extends TestCase
{
    public function test_it_carries_its_parts(): void
    {
        $at   = new DateTimeImmutable('2026-10-10 12:00:00');
        $unit = new UsageUnit('tenant', 'monthly_active_contacts', 'contact:abc', $at);

        $this->assertSame('tenant', $unit->tenantId);
        $this->assertSame('monthly_active_contacts', $unit->key);
        $this->assertSame('contact:abc', $unit->unitKey);
        $this->assertSame($at, $unit->occurredAt);
    }

    public function test_it_accepts_the_longest_unit_key(): void
    {
        $unit = new UsageUnit('t', 'k', str_repeat('a', 191), new DateTimeImmutable());

        $this->assertSame(191, mb_strlen($unit->unitKey));
    }

    public function test_it_rejects_a_unit_key_that_is_too_long(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new UsageUnit('t', 'k', str_repeat('a', 192), new DateTimeImmutable());
    }

    public function test_it_rejects_an_empty_unit_key(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new UsageUnit('t', 'k', '', new DateTimeImmutable());
    }

    public function test_it_rejects_an_empty_tenant_or_key(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new UsageUnit('', 'k', 'u', new DateTimeImmutable());
    }
}
