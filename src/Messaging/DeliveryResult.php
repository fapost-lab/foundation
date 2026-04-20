<?php

declare(strict_types=1);

namespace FAPost\Foundation\Messaging;

use Spatie\LaravelData\Data;

/**
 * Result of a single outbound delivery attempt.
 */
final class DeliveryResult extends Data
{
    public function __construct(
        public readonly bool $sent,
        public readonly ?string $providerMessageId = null,
        public readonly bool $duplicate = false,
        public readonly ?string $error = null,
    ) {
    }
}
