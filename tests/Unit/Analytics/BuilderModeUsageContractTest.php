<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Analytics;

use DateTimeImmutable;
use Fapost\Foundation\Analytics\DTO\BuilderModeUsage;
use Fapost\Foundation\Analytics\Events\BuilderModeChanged;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class BuilderModeUsageContractTest extends TestCase
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function invalidChanges(): array
    {
        return [
            'unknown mode'          => ['canvas', 'tree'],
            'unknown previous mode' => ['graph', ''],
            'upper case mode'       => ['GRAPH', 'tree'],
            'same mode'             => ['graph', 'graph'],
        ];
    }

    public function test_the_modes_keep_their_wire_values(): void
    {
        $this->assertSame('tree', BuilderModeUsage::TREE);
        $this->assertSame('graph', BuilderModeUsage::GRAPH);
        $this->assertSame(['tree', 'graph'], BuilderModeUsage::MODES);
    }

    public function test_the_usage_carries_its_counts_and_their_total(): void
    {
        $usage = new BuilderModeUsage(tree: 5, graph: 2);

        $this->assertSame(5, $usage->tree);
        $this->assertSame(2, $usage->graph);
        $this->assertSame(7, $usage->total);
        $this->assertSame(0, new BuilderModeUsage(0, 0)->total);
    }

    public function test_a_negative_count_is_refused(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new BuilderModeUsage(tree: -1, graph: 0);
    }

    public function test_a_change_carries_its_values(): void
    {
        $at    = new DateTimeImmutable('2026-11-23 10:00:00');
        $event = new BuilderModeChanged('01jabc00000000000000000000', 'graph', 'tree', $at);

        $this->assertSame('01jabc00000000000000000000', $event->tenantId);
        $this->assertSame('graph', $event->mode);
        $this->assertSame('tree', $event->previousMode);
        $this->assertSame($at, $event->occurredAt);
    }

    #[DataProvider('invalidChanges')]
    public function test_an_invalid_change_is_refused(string $mode, string $previousMode): void
    {
        $this->expectException(InvalidArgumentException::class);

        new BuilderModeChanged('01jabc00000000000000000000', $mode, $previousMode, new DateTimeImmutable());
    }
}
