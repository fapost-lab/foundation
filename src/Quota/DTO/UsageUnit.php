<?php

declare(strict_types=1);

namespace Fapost\Foundation\Quota\DTO;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * One unit of use of a per-period limit, as passed to
 * {@see \Fapost\Foundation\Quota\Contracts\UsageMeterInterface::consume()}. Each unit counts as 1.
 */
final readonly class UsageUnit
{
    public const int MAX_UNIT_KEY_LENGTH = 191;

    /**
     * @param  string  $tenantId  tenant ULID in its lowercase RFC 4122 form
     * @param  string  $key  a key registered with kind PerPeriod
     * @param  string  $unitKey  the natural key of the unit, at most 191 characters; the same on every retry of the same unit; no personal identifiers (hash them)
     * @param  DateTimeImmutable  $occurredAt  when the unit was used; picks the period; the same on every retry where the caller has a stable time, otherwise now
     *
     * @throws InvalidArgumentException when the tenant id, key or unit key is empty or the unit key is too long
     */
    public function __construct(
        public string $tenantId,
        public string $key,
        public string $unitKey,
        public DateTimeImmutable $occurredAt,
    ) {
        if ('' === $tenantId || '' === $key) {
            throw new InvalidArgumentException('A usage unit needs a tenant id and a key.');
        }

        if ('' === $unitKey || mb_strlen($unitKey) > self::MAX_UNIT_KEY_LENGTH) {
            throw new InvalidArgumentException(sprintf(
                'The unit key must be 1 to %d characters, got %d.',
                self::MAX_UNIT_KEY_LENGTH,
                mb_strlen($unitKey),
            ));
        }
    }
}
