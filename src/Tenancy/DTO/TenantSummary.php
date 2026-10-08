<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tenancy\DTO;

use DateTimeImmutable;
use Fapost\Foundation\Tenancy\Contracts\TenantDirectoryInterface;
use Fapost\Foundation\Tenancy\Enums\TenantStatus;

/**
 * A tenant as a {@see TenantDirectoryInterface} returns it.
 */
final readonly class TenantSummary
{
    public function __construct(
        /** Tenant ULID in its lowercase RFC 4122 form; the key a caller links its own data to. */
        public string $id,
        public string $slug,
        public TenantStatus $status,
        /** UTC; null for rows that carry no creation time. */
        public ?DateTimeImmutable $createdAt,
        /**
         * Absolute URL of the tenant's host root, with a trailing slash. Built by Core. It addresses
         * this tenant only when the platform resolves tenants by host; with a single tenant per
         * installation every tenant shares the application URL.
         */
        public string $url,
        /** Absolute URL of the admin panel login on the tenant's host, as in ProvisionedTenant. Built by Core. */
        public string $loginUrl,
    ) {
    }
}
