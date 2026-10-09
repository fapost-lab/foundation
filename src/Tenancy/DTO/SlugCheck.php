<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tenancy\DTO;

use Fapost\Foundation\Tenancy\Enums\SlugAvailability;
use Fapost\Foundation\Tenancy\Enums\SlugProblem;

/**
 * The answer to "can this slug be reserved?". Advisory: only a reservation decides.
 */
final readonly class SlugCheck
{
    public function __construct(
        public string $slug,
        public SlugAvailability $availability,
        /** Set only when the availability is `Invalid`. */
        public ?SlugProblem $problem = null,
    ) {
    }

    public function isAvailable(): bool
    {
        return SlugAvailability::Available === $this->availability;
    }
}
