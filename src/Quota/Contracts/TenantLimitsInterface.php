<?php

declare(strict_types=1);

namespace Fapost\Foundation\Quota\Contracts;

/**
 * How much of a registered limit a tenant may use. Implemented by an operator package; Core's
 * default allows everything, so an installation without one behaves as if no limits existed.
 *
 * The implementation only answers the limit. The platform counts what the tenant uses (its records
 * live in the tenant's own storage) and refuses work over the limit.
 *
 * Called on creation paths, so an implementation should answer quickly (cache what it reads).
 */
interface TenantLimitsInterface
{
    /**
     * @param  string  $tenantId  tenant ULID in its lowercase RFC 4122 form
     * @param  string  $key  a key registered in {@see LimitRegistryInterface}
     *
     * @return int|null the limit, at least 0; null means no limit
     */
    public function limitFor(string $tenantId, string $key): ?int;
}
