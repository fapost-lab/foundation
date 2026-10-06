<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tenancy\DTO;

use DateTimeImmutable;
use Fapost\Foundation\Tenancy\Enums\TenantStatus;

/**
 * A tenant as a {@see \Fapost\Foundation\Tenancy\Contracts\TenantDirectoryInterface} returns it.
 */
final readonly class TenantSummary
{
    public function __construct(
        /** Tenant ULID; the key a caller links its own data to. */
        public string $id,
        public string $slug,
        public TenantStatus $status,
        /** UTC; null for rows that carry no creation time. */
        public ?DateTimeImmutable $createdAt,
        /** Absolute URL of the tenant's host root. Built by Core. */
        public string $url,
        /** Absolute URL of the admin panel login on the tenant's host. Built by Core. */
        public string $adminLoginUrl,
    ) {
    }
}
