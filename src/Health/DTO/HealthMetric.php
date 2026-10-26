<?php

declare(strict_types=1);

namespace Fapost\Foundation\Health\DTO;

use Fapost\Foundation\Health\Enums\HealthUnit;
use InvalidArgumentException;

/**
 * One figure a probe measured.
 *
 * The names a service reports are fixed per service, and new ones may be added in a minor release:
 *
 * | Service            | Name               | Unit         | Subject       |
 * |--------------------|--------------------|--------------|---------------|
 * | `reverb`           | `connections`      | `count`      | —             |
 * | `reverb`           | `channels`         | `count`      | —             |
 * | `gateway`          | `spec_status`      | `text`       | platform      |
 * | `gateway`          | `drifted_channels` | `count`      | platform      |
 * | `webhook_registry` | `missing`          | `count`      | —             |
 * | `webhook_registry` | `stale`            | `count`      | —             |
 * | `webhook_registry` | `orphaned`         | `count`      | —             |
 * | `queues`           | `wait_seconds`     | `seconds`    | queue         |
 * | `queues`           | `throughput`       | `per_minute` | queue         |
 * | `queues`           | `failed_jobs`      | `count`      | —             |
 *
 * `spec_status` is `matches`, `missing` or `differs`. A subject is a queue's or a platform's name, never
 * a tenant's id or a channel's name.
 *
 * @experimental
 */
final readonly class HealthMetric
{
    /**
     * @param  string  $name  lower-case letters and underscores, from the table above
     * @param  int|float|string|null  $value  null when it could not be measured, never 0 in its place
     * @param  string|null  $subject  what the figure is about when a service reports it more than once
     *
     * @throws InvalidArgumentException when the name is not lower-case letters and underscores, or the subject is empty
     */
    public function __construct(
        public string $name,
        public int|float|string|null $value,
        public HealthUnit $unit,
        public ?string $subject = null,
    ) {
        if (1 !== preg_match('/^[a-z][a-z_]*$/', $name)) {
            throw new InvalidArgumentException(sprintf('A health metric name must be lower-case letters and underscores, "%s" given.', $name));
        }

        if (null !== $subject && '' === mb_trim($subject)) {
            throw new InvalidArgumentException('A health metric subject cannot be empty; pass null for none.');
        }
    }
}
