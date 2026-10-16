<?php

declare(strict_types=1);

namespace Fapost\Foundation\Solution\Contracts;

use Fapost\Foundation\Solution\DTO\SolutionConnectResult;
use Fapost\Foundation\Solution\DTO\SolutionOffer;

/**
 * The Solutions an operator offers a tenant to connect, and the act of connecting one. Implemented by an operator
 * package; Core's default offers nothing, so an installation without one shows no offers.
 *
 * It is the cold half of what a tenant may have: Core asks it only from the Solutions screen and its Connect button,
 * never while running a tenant's jobs. Whether a Solution runs is still answered by
 * {@see SolutionEntitlementInterface} alone; a successful connect shows there as a grant.
 *
 * Implementation (operator package)
 * - MAY: offer Solutions this image does not install (Core intersects with what it has); start a trial or record a
 *   request on connect; write its own tables.
 * - MUST: offer nothing for a Solution the tenant is already granted; make `connect` idempotent (a repeated call
 *   answers `AlreadyPending`, or `TrialStarted` without a second trial); show a connect in the next entitlement answer
 *   (invalidate its own cache); answer `refused` with a reason rather than throw when the tenant may not connect it.
 * - MUST NOT: write Core's tables or a tenant schema; charge money through this call.
 *
 * Caller (Core)
 * - MUST: call it only on behalf of a staff user of the tenant who may manage its Solutions; refuse to connect while
 *   the entitlement answer cannot be had; forget its memo of the tenant's entitlement after a connect.
 * - MUST NOT: call it from a queue job or the flow runtime.
 *
 * @experimental
 */
interface SolutionStorefrontInterface
{
    /**
     * @param  string  $tenantId  tenant ULID in its lowercase RFC 4122 form
     *
     * @return list<SolutionOffer> at most one offer per Solution
     */
    public function offersFor(string $tenantId): array;

    /**
     * @param  string  $tenantId  tenant ULID in its lowercase RFC 4122 form
     * @param  string  $requestedBy  who asked, as the operator's history should name them (a staff user's name)
     */
    public function connect(string $tenantId, string $solutionId, string $requestedBy): SolutionConnectResult;
}
