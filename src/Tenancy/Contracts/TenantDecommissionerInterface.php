<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tenancy\Contracts;

use Fapost\Foundation\Tenancy\DTO\DecommissionedTenant;
use Fapost\Foundation\Tenancy\Enums\DecommissionReason;
use Fapost\Foundation\Tenancy\Events\TenantDecommissioned;
use Fapost\Foundation\Tenancy\Exceptions\TenantDecommissionFailedException;

/**
 * Removes a tenant completely and irreversibly.
 *
 * Implemented by Core; extension and operator packages only call it. It removes the provider-side channel
 * webhooks, the webhook routing, the support access tokens, the media the installation holds (never objects in
 * a client's own storage), the tenant's schema and its landlord row, so the slug is free at once. No backup or
 * dump is made: a caller asks the owner or the operator to confirm first. When it is done Core dispatches
 * {@see TenantDecommissioned}.
 *
 * Slow (provider calls, file deletion, dropping a schema): run it from a queued job, not from an HTTP request.
 *
 * @experimental
 */
interface TenantDecommissionerInterface
{
    /**
     * Idempotent: an unknown tenant is reported as `alreadyGone` (and no event is dispatched), so a job that
     * crashed after the call can repeat it. A Pending tenant (being provisioned) is refused.
     *
     * @param  string  $tenantId  tenant ULID in its lowercase RFC 4122 form
     *
     * @throws TenantDecommissionFailedException
     */
    public function decommission(string $tenantId, DecommissionReason $reason): DecommissionedTenant;
}
