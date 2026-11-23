<?php

declare(strict_types=1);

namespace Fapost\Foundation\Analytics\Events;

use DateTimeImmutable;
use Fapost\Foundation\Analytics\DTO\BuilderModeUsage;
use InvalidArgumentException;

/**
 * Dispatched by Core after a staff user switched the flow builder to another mode and the choice was saved.
 *
 * Choosing the mode one already has is not a switch and dispatches nothing; a user who never chose a mode is on the
 * tree. It names no staff user. An operator package may listen to follow the switches over time between reads of
 * {@see \Fapost\Foundation\Analytics\Contracts\BuilderModeUsageInterface}; it must not rebuild the current counts
 * from it alone, since deactivating or removing a user dispatches nothing.
 *
 * @experimental
 */
final readonly class BuilderModeChanged
{
    /**
     * @throws InvalidArgumentException when a mode is not one of {@see BuilderModeUsage::MODES} or both are the same
     */
    public function __construct(
        /** Tenant ULID in its lowercase RFC 4122 form. */
        public string $tenantId,
        /** The mode chosen now: {@see BuilderModeUsage::TREE} or {@see BuilderModeUsage::GRAPH}. */
        public string $mode,
        /** The mode the user had before; {@see BuilderModeUsage::TREE} when they had never chosen one. */
        public string $previousMode,
        public DateTimeImmutable $occurredAt,
    ) {
        if (! in_array($mode, BuilderModeUsage::MODES, true) || ! in_array($previousMode, BuilderModeUsage::MODES, true)) {
            throw new InvalidArgumentException('A builder mode is "tree" or "graph".');
        }

        if ($mode === $previousMode) {
            throw new InvalidArgumentException('A builder mode change needs a mode other than the previous one.');
        }
    }
}
