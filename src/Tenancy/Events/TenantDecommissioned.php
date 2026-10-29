<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tenancy\Events;

use Fapost\Foundation\Tenancy\Enums\DecommissionReason;

/**
 * Dispatched by Core after a tenant was removed completely.
 *
 * Operator packages listen to erase their own data about the tenant. It is not dispatched when the tenant was
 * already gone. The tenant no longer exists when it arrives, so a listener works from these values alone and
 * must be idempotent: a failed run of the removal may dispatch it again on the next attempt.
 *
 * @experimental
 */
final readonly class TenantDecommissioned
{
    public function __construct(
        /** Tenant ULID in its lowercase RFC 4122 form. */
        public string $tenantId,
        /** The slug the tenant had; it is free again. */
        public string $slug,
        public DecommissionReason $reason,
    ) {
    }
}
