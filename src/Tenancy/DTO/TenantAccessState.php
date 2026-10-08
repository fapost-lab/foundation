<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tenancy\DTO;

use Fapost\Foundation\Tenancy\Enums\AccessMode;

/**
 * A tenant's access mode and what its staff are told about it.
 */
final readonly class TenantAccessState
{
    public function __construct(
        public AccessMode $mode,
        public ?AccessNotice $notice = null,
    ) {}

    public static function active(): self
    {
        return new self(AccessMode::Active);
    }

    public function isStopped(): bool
    {
        return $this->mode === AccessMode::Stopped;
    }
}
