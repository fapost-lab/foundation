<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tenancy\Contracts;

/**
 * Answers whether a tenant's own owner may delete the workspace from its settings.
 *
 * Implemented by an operator package; Core's default answers false, so a self-hosted installation never offers
 * it.
 *
 * Fast: Core asks while rendering the tenant's settings and when the owner confirms, so it may be called from an
 * HTTP request and an implementation should answer quickly.
 *
 * @experimental
 */
interface TenantSelfDeletionPolicyInterface
{
    /**
     * @param  string  $tenantId  tenant ULID in its lowercase RFC 4122 form
     */
    public function allows(string $tenantId): bool;
}
