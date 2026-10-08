<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tenancy\DTO;

use Fapost\Foundation\Tenancy\Contracts\TenantProvisionerInterface;

/**
 * A tenant created by a {@see TenantProvisionerInterface}.
 */
final readonly class ProvisionedTenant
{
    public function __construct(
        /** Tenant ULID; the key a caller links its own data to. */
        public string $id,
        public string $slug,
        /** Absolute URL of the admin panel login on the tenant's host. Built by Core. */
        public string $loginUrl,
    ) {}
}
