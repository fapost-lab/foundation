<?php

declare(strict_types=1);

namespace Fapost\Foundation\Analytics\Contracts;

use Fapost\Foundation\Analytics\DTO\BuilderModeUsage;

/**
 * Reports which flow builder mode (tree or graph) a tenant's staff users have set now, so an operator package can
 * see how the two layouts are used. Implemented by Core; an operator package calls it outside any tenant context.
 *
 * Who is counted: the staff users that take a place under the `staff` limit, i.e. every account not deactivated,
 * invited ones that have not activated yet included, without the platform's support user. The numbers therefore
 * add up to what {@see \Fapost\Foundation\Quota\Contracts\TenantUsageInterface} reports for `staff`. A user who
 * never chose a mode works in the tree and is counted there.
 *
 * Implementation (Core)
 * - MAY: switch to the tenant's storage for the duration of the call and count there with an aggregate query.
 * - MUST: restore the caller's tenant context before returning, also when counting throws; answer null for a
 *   tenant that is unknown or has no storage yet.
 * - MUST NOT: write anything; keep any tenant's data in object properties between calls.
 *
 * Caller (operator package)
 * - MAY: ask for any tenant; listen to {@see \Fapost\Foundation\Analytics\Events\BuilderModeChanged} for the
 *   switches between the calls.
 * - MUST: cache the answer and not call it for every row of a list, each call can switch to the tenant's storage
 *   and run a query; treat null as "not reported", not as zero.
 *
 * @experimental
 */
interface BuilderModeUsageInterface
{
    /**
     * How many counted staff users of the tenant have each builder mode now.
     *
     * Null when the tenant is unknown, the id is not a tenant id, or the tenant has no storage yet (Pending).
     */
    public function usageFor(string $tenantId): ?BuilderModeUsage;
}
