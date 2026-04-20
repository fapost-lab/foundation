<?php

declare(strict_types=1);

namespace FAPost\Foundation\Flow\DTO;

final readonly class TriggerContext
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public string $type,
        public string $tenantId,
        public ?string $assistantId,
        public array $payload,
    ) {
    }
}
