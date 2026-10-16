<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Quota;

use DateTimeImmutable;
use Fapost\Foundation\Quota\DTO\PeriodUsage;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PeriodUsageTest extends TestCase
{
    /**
     * @return array<string, array{string, int, ?int, string, string}>
     */
    public static function invalidUsages(): array
    {
        return [
            'empty key'       => [' ', 1, 10, '2026-10-01', '2026-11-01'],
            'negative used'   => ['calls', -1, 10, '2026-10-01', '2026-11-01'],
            'negative limit'  => ['calls', 1, -1, '2026-10-01', '2026-11-01'],
            'empty period'    => ['calls', 1, 10, '2026-10-01', '2026-10-01'],
            'reversed period' => ['calls', 1, 10, '2026-11-01', '2026-10-01'],
        ];
    }

    public function test_a_usage_keeps_its_values(): void
    {
        $usage = new PeriodUsage('calls', 120, 100, new DateTimeImmutable('2026-10-01'), new DateTimeImmutable('2026-11-01'));

        $this->assertSame('calls', $usage->key);
        $this->assertSame(120, $usage->used);
        $this->assertSame(100, $usage->limit);
    }

    public function test_a_usage_without_a_limit_and_with_a_zero_limit(): void
    {
        $start = new DateTimeImmutable('2026-10-01');
        $end   = new DateTimeImmutable('2026-11-01');

        $this->assertNull((new PeriodUsage('calls', 0, null, $start, $end))->limit);
        $this->assertSame(0, (new PeriodUsage('calls', 0, 0, $start, $end))->limit);
    }

    #[DataProvider('invalidUsages')]
    public function test_an_invalid_usage_is_rejected(string $key, int $used, ?int $limit, string $start, string $end): void
    {
        $this->expectException(InvalidArgumentException::class);

        new PeriodUsage($key, $used, $limit, new DateTimeImmutable($start), new DateTimeImmutable($end));
    }
}
