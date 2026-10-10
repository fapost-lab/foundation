<?php

declare(strict_types=1);

namespace Fapost\Foundation\Quota\Contracts;

/**
 * Reports how much of a counted limit a tenant uses now, so an operator package can show
 * "used of limit". Implemented by Core; an operator package calls it outside any tenant context.
 *
 * It serves limits registered with {@see \Fapost\Foundation\Quota\Enums\LimitKind::Records} and
 * {@see \Fapost\Foundation\Quota\Enums\LimitKind::Bytes}: the platform counts them in the tenant's
 * own storage, which the operator cannot read. A {@see \Fapost\Foundation\Quota\Enums\LimitKind::PerPeriod}
 * key is always null here, that use is counted by the operator through {@see UsageMeterInterface}.
 *
 * Implementation (Core)
 * - MAY: switch to the tenant's storage for the duration of the call and count there; count
 *   with an aggregate query.
 * - MUST: restore the caller's tenant context before returning, also when counting throws;
 *   count the same way the platform counts when it enforces the limit, so the report and the
 *   refusal never disagree; answer null for a tenant that is unknown or has no storage yet and
 *   for a key it does not count.
 * - MUST NOT: write anything; keep any tenant's data in object properties between calls.
 *
 * Caller (operator package)
 * - MAY: ask for any key of any tenant.
 * - MUST: cache the answer and not call it for every row of a list, each call can switch to the
 *   tenant's storage and run a query; treat null as "not reported", not as zero.
 * - MUST NOT: use the number to enforce a limit, enforcement happens in the platform on the path
 *   that creates the record or stores the bytes.
 */
interface TenantUsageInterface
{
    /**
     * How much of a Records or Bytes limit the tenant uses now, counted by the platform in the
     * tenant's own storage: a number of records, or bytes.
     *
     * Null when the platform does not report this key, or the tenant is unknown or has no storage
     * yet (Pending).
     */
    public function current(string $tenantId, string $key): ?int;
}
