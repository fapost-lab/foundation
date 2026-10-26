<?php

declare(strict_types=1);

namespace Fapost\Foundation\Health\DTO;

use DateTimeImmutable;
use Fapost\Foundation\Health\Enums\HealthState;
use Fapost\Foundation\Health\Enums\MonitoredService;
use InvalidArgumentException;

/**
 * One service's health: the state its probe established, why, when, and what it measured.
 *
 * Nothing here is a secret or names a tenant: the reason and the endpoint carry no credential, no
 * user name or password of a URL and no query string, and the metrics carry counts, not channel names.
 *
 * @experimental
 */
final readonly class ServiceHealth
{
    /**
     * @param  string  $reason  one English sentence for an operator
     * @param  DateTimeImmutable  $checkedAt  when the probe ran; up to its cache lifetime (seconds) before the report
     * @param  string|null  $endpoint  what was asked, `host:port` or `scheme://host[:port]/path`; null when nothing was
     * @param  list<HealthMetric>  $metrics  see {@see HealthMetric} for the names each service reports
     *
     * @throws InvalidArgumentException on an empty reason or endpoint, or a metric that is not a HealthMetric
     */
    public function __construct(
        public MonitoredService $service,
        public HealthState $state,
        public string $reason,
        public DateTimeImmutable $checkedAt,
        public ?string $endpoint = null,
        public array $metrics = [],
    ) {
        if ('' === mb_trim($reason)) {
            throw new InvalidArgumentException('A service health needs a reason.');
        }

        if (null !== $endpoint && '' === mb_trim($endpoint)) {
            throw new InvalidArgumentException('A service health endpoint cannot be empty; pass null for none.');
        }

        foreach ($metrics as $metric) {
            if (! $metric instanceof HealthMetric) {
                throw new InvalidArgumentException('A service health holds HealthMetric entries only.');
            }
        }
    }

    /**
     * The first metric of that name (and subject, when given), or null when the service did not report it.
     */
    public function metric(string $name, ?string $subject = null): ?HealthMetric
    {
        foreach ($this->metrics as $metric) {
            if ($metric->name === $name && (null === $subject || $metric->subject === $subject)) {
                return $metric;
            }
        }

        return null;
    }
}
