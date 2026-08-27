<?php

declare(strict_types=1);

namespace Fapost\Foundation\Analytics\DTO;

use DateTimeImmutable;
use Fapost\Foundation\Analytics\Enums\AnalyticsEventType;

final readonly class AnalyticsEvent
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public string $tenantId,
        public AnalyticsEventType $eventType,
        public array $payload,
        public DateTimeImmutable $occurredAt,
    ) {
    }
}
