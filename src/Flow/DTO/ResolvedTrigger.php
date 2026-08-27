<?php

declare(strict_types=1);

namespace Fapost\Foundation\Flow\DTO;

final readonly class ResolvedTrigger
{
    /**
     * @param  array<string, mixed>  $config
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public string $triggerId,
        public string $flowId,
        public string $type,
        public array $config = [],
        public array $metadata = [],
    ) {
    }
}
