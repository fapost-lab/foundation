<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tenancy\Contracts;

use Fapost\Foundation\Tenancy\DTO\RenamedTenant;
use Fapost\Foundation\Tenancy\Exceptions\TenantRenameFailedException;

/**
 * Changes a tenant's slug, and with it the tenant's host.
 *
 * Implemented by Core; extension and operator packages only call it. The tenant's data, schema,
 * channel webhooks and tokens are not touched; staff are signed out while the session cookie is
 * host-only, as host mode requires (it belongs to the old host).
 *
 * The previous host redirects GET and HEAD requests (HTTP 302) to the new one for a configured
 * period, only while the tenant is Active (an Inactive or Suspended tenant can be renamed; its old host
 * answers 404), and stays reserved for this tenant afterwards: no other tenant can take it. Core notifies
 * nobody; telling the tenant's staff the new address is the caller's job.
 *
 * Fast (one landlord transaction): may be called from an HTTP request.
 */
interface TenantRenamerInterface
{
    /**
     * Renaming to the slug the tenant already has succeeds and changes nothing (`changed` is false),
     * so a caller that crashed after the call can repeat it. Pending tenants cannot be renamed;
     * Active, Inactive and Suspended ones can.
     *
     * @throws TenantRenameFailedException
     */
    public function rename(string $tenantId, string $newSlug): RenamedTenant;
}
