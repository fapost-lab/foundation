<?php

declare(strict_types=1);

namespace Fapost\Foundation\Quota\Contracts;

use Fapost\Foundation\Quota\DTO\PeriodUsageReport;

/**
 * Reports how much of each per-period limit a tenant has used in its current period, so the tenant's
 * admins can see "used of limit" in the panel. Implemented by an operator package; Core's default
 * returns null, so an installation without one shows no per-period usage.
 *
 * Implementation (operator package)
 * - MAY: read its own tables; cache the report per tenant for a short time (Core shows what it gets);
 *   report only the keys it limits or counts; give one action (an upgrade page) or none.
 * - MUST: answer for the period that contains "now", with the same used, limit and period end its
 *   {@see UsageMeterInterface} counts and refuses by, so the screen and the refusal never disagree;
 *   answer cheaply, one indexed query or a cache hit, because Core calls it while rendering a page;
 *   return null for an unknown tenant; give an absolute http(s) action URL.
 * - MUST NOT: count or record anything (it is a read); report Records or Bytes keys (Core counts them,
 *   see {@see TenantUsageInterface}); throw for "nothing to report" (null is the answer); call Core's
 *   classes, write Core's tables or a tenant schema; keep any tenant's data in object properties
 *   between calls.
 *
 * Caller (Core)
 * - MUST: call at most once per page view, never per key and never on a gate or a job's hot path;
 *   report an exception and show every per-period row as unavailable (not "no data", which is null or
 *   an absent key), a faulty operator never breaks the page;
 *   ignore entries whose key is not a registered per-period key.
 * - MUST NOT: use the numbers to enforce anything, enforcement stays in {@see UsageMeterInterface}.
 *
 * Why: the operator owns the period and the counter; Core owns the screen and the people who read it.
 * One call per page keeps the operator's storage off every request.
 */
interface PeriodUsageInterface
{
    /**
     * @param  string  $tenantId  tenant ULID in its lowercase RFC 4122 form
     * @param  string  $locale  the locale of the person reading, for the action label
     * @return PeriodUsageReport|null null when the operator reports nothing for this tenant
     */
    public function reportFor(string $tenantId, string $locale): ?PeriodUsageReport;
}
