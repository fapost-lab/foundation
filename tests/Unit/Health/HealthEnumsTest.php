<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Health;

use Fapost\Foundation\Health\Enums\HealthState;
use Fapost\Foundation\Health\Enums\HealthUnit;
use Fapost\Foundation\Health\Enums\MonitoredService;
use PHPUnit\Framework\TestCase;

final class HealthEnumsTest extends TestCase
{
    public function test_states_keep_their_published_values(): void
    {
        $this->assertSame(['up', 'down', 'unknown', 'disabled'], array_column(HealthState::cases(), 'value'));
    }

    public function test_services_keep_their_published_values(): void
    {
        $this->assertSame(['reverb', 'gateway', 'webhook_registry', 'queues'], array_column(MonitoredService::cases(), 'value'));
    }

    public function test_units_keep_their_published_values(): void
    {
        $this->assertSame(['count', 'seconds', 'per_minute', 'text'], array_column(HealthUnit::cases(), 'value'));
    }
}
