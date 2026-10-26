<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Health;

use DateTimeImmutable;
use Fapost\Foundation\Health\DTO\HealthMetric;
use Fapost\Foundation\Health\DTO\InstallationHealthReport;
use Fapost\Foundation\Health\DTO\ServiceHealth;
use Fapost\Foundation\Health\Enums\HealthState;
use Fapost\Foundation\Health\Enums\HealthUnit;
use Fapost\Foundation\Health\Enums\MonitoredService;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class InstallationHealthReportTest extends TestCase
{
    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidNames(): iterable
    {
        yield 'empty' => [''];
        yield 'upper case' => ['Connections'];
        yield 'dash' => ['wait-seconds'];
        yield 'leading underscore' => ['_count'];
    }
    public function test_a_report_finds_a_service_and_its_metrics(): void
    {
        $reverb = new ServiceHealth(
            MonitoredService::Reverb,
            HealthState::Up,
            'Reverb answered at reverb:8080.',
            new DateTimeImmutable(),
            'reverb:8080',
            [
                new HealthMetric('connections', 3, HealthUnit::Count),
                new HealthMetric('drifted_channels', 1, HealthUnit::Count, 'telegram'),
                new HealthMetric('drifted_channels', 2, HealthUnit::Count, 'viber'),
            ],
        );
        $report = new InstallationHealthReport([$reverb], new DateTimeImmutable());

        $this->assertSame($reverb, $report->service(MonitoredService::Reverb));
        $this->assertNull($report->service(MonitoredService::Queues));
        $this->assertSame(3, $reverb->metric('connections')?->value);
        $this->assertSame(2, $reverb->metric('drifted_channels', 'viber')?->value);
        $this->assertNull($reverb->metric('channels'));
    }

    public function test_a_service_is_reported_once(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new InstallationHealthReport([$this->gateway(), $this->gateway()], new DateTimeImmutable());
    }

    public function test_a_report_holds_service_health_only(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new InstallationHealthReport(['gateway'], new DateTimeImmutable());
    }

    public function test_a_service_health_needs_a_reason(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ServiceHealth(MonitoredService::Gateway, HealthState::Unknown, ' ', new DateTimeImmutable());
    }

    public function test_a_service_health_endpoint_is_null_or_not_empty(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ServiceHealth(MonitoredService::Gateway, HealthState::Up, 'Answered.', new DateTimeImmutable(), '');
    }

    public function test_a_service_health_holds_metrics_only(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ServiceHealth(MonitoredService::Gateway, HealthState::Up, 'Answered.', new DateTimeImmutable(), null, [['connections' => 1]]);
    }

    public function test_a_metric_may_be_unmeasured(): void
    {
        $this->assertNull((new HealthMetric('wait_seconds', null, HealthUnit::Seconds, 'default'))->value);
    }

    #[DataProvider('invalidNames')]
    public function test_a_metric_name_is_lower_case_letters_and_underscores(string $name): void
    {
        $this->expectException(InvalidArgumentException::class);

        new HealthMetric($name, 1, HealthUnit::Count);
    }

    public function test_a_metric_subject_is_null_or_not_empty(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new HealthMetric('throughput', 1, HealthUnit::PerMinute, '');
    }

    private function gateway(): ServiceHealth
    {
        return new ServiceHealth(MonitoredService::Gateway, HealthState::Up, 'Answered.', new DateTimeImmutable());
    }
}
