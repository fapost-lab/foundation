<?php

declare(strict_types=1);

namespace Fapost\Foundation\Health\DTO;

use DateTimeImmutable;
use Fapost\Foundation\Health\Enums\MonitoredService;
use InvalidArgumentException;

/**
 * The health of the whole installation, as {@see \Fapost\Foundation\Health\Contracts\InstallationHealthInterface}
 * reports it.
 *
 * @experimental
 */
final readonly class InstallationHealthReport
{
    /**
     * @param  list<ServiceHealth>  $services  one entry per service this installation probes, each service once
     *
     * @throws InvalidArgumentException on an entry that is not a ServiceHealth, or a service reported twice
     */
    public function __construct(
        public array $services,
        public DateTimeImmutable $generatedAt,
    ) {
        $seen = [];

        foreach ($services as $entry) {
            if (! $entry instanceof ServiceHealth) {
                throw new InvalidArgumentException('An installation health report holds ServiceHealth entries only.');
            }

            if (isset($seen[$entry->service->value])) {
                throw new InvalidArgumentException(sprintf('The service "%s" is reported twice.', $entry->service->value));
            }

            $seen[$entry->service->value] = true;
        }
    }

    /**
     * The service's health, or null when this installation does not probe it. Null is not healthy: show it as
     * "not monitored", never as up.
     */
    public function service(MonitoredService $service): ?ServiceHealth
    {
        foreach ($this->services as $entry) {
            if ($entry->service === $service) {
                return $entry;
            }
        }

        return null;
    }
}
