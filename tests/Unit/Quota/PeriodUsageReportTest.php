<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Quota;

use DateTimeImmutable;
use Fapost\Foundation\Quota\DTO\PeriodUsage;
use Fapost\Foundation\Quota\DTO\PeriodUsageReport;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PeriodUsageReportTest extends TestCase
{
    /**
     * @return array<string, array{?string, ?string}>
     */
    public static function invalidActions(): array
    {
        return [
            'label without url' => ['Upgrade', null],
            'url without label' => [null, 'https://example.test'],
            'empty label'       => [' ', 'https://example.test'],
            'javascript url'    => ['Upgrade', 'javascript:alert(1)'],
            'relative url'      => ['Upgrade', '/billing'],
            'no host'           => ['Upgrade', 'https://'],
            'empty host'        => ['Upgrade', 'http:///path'],
            'whitespace'        => ['Upgrade', 'https://example.test/a b'],
            'control character' => ['Upgrade', "https://example.test/\n"],
            'scheme only'       => ['Upgrade', 'https:example.test'],
        ];
    }

    public function test_an_empty_report(): void
    {
        $report = new PeriodUsageReport();

        $this->assertSame([], $report->usage);
        $this->assertNull($report->actionLabel);
        $this->assertNull($report->find('calls'));
    }

    public function test_find_returns_the_entry_for_a_key(): void
    {
        $calls  = $this->usage('calls');
        $report = new PeriodUsageReport([$calls, $this->usage('messages')], 'Upgrade', 'https://example.test/billing');

        $this->assertSame($calls, $report->find('calls'));
        $this->assertNull($report->find('missing'));
        $this->assertSame('Upgrade', $report->actionLabel);
        $this->assertSame('https://example.test/billing', $report->actionUrl);
    }

    public function test_a_duplicate_key_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new PeriodUsageReport([$this->usage('calls'), $this->usage('calls')]);
    }

    public function test_an_entry_that_is_not_a_period_usage_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new PeriodUsageReport(['calls']);
    }

    #[DataProvider('invalidActions')]
    public function test_an_invalid_action_is_rejected(?string $label, ?string $url): void
    {
        $this->expectException(InvalidArgumentException::class);

        new PeriodUsageReport([], $label, $url);
    }

    private function usage(string $key): PeriodUsage
    {
        return new PeriodUsage($key, 1, 10, new DateTimeImmutable('2026-10-01'), new DateTimeImmutable('2026-11-01'));
    }
}
