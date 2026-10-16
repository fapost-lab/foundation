<?php

declare(strict_types=1);

namespace Fapost\Foundation\Solution\Contracts;

use Fapost\Foundation\Solution\DTO\SolutionEntitlement;

/**
 * Answers which installed Solutions a tenant may run. Implemented by an operator package; Core's default answers
 * {@see SolutionEntitlement::all()}, so an installation without one behaves as if every tenant had every Solution.
 *
 * Losing a Solution is an answer, not an action: Core computes a Solution as running when it is installed and either
 * granted ({@see SolutionEntitlement::grants()}: the operator turns it on, a plan's bundle or a running add-on) or
 * allowed and turned on by the tenant. A Solution the answer no longer lists stops running with its activation and its
 * data kept, and runs again, with nothing written, as soon as the answer lists it again.
 *
 * Implementation (operator package)
 * - MAY: read its own tables; cache the answer per tenant across processes; list Solutions this image does not
 *   install; give one button (a plans or contact page) or none; grant a Solution (with its source and an end for
 *   display) instead of only allowing it.
 * - MUST: answer quickly, a cache hit or one indexed query, because Core asks while running a tenant's jobs; show a
 *   change (an add-on detached or renewed) no later than its own cache invalidation; grant only what must run without
 *   the tenant's action; return {@see SolutionEntitlement::all()} only when nothing is restricted; answer `only([])`
 *   for an unknown tenant.
 * - MUST NOT: write Core's tables or a tenant schema, or call Core's classes to turn a Solution off; keep any
 *   tenant's data in object properties between calls; throw for "nothing entitled" (that is `only([])`).
 *
 * Caller (Core)
 * - MUST: ask at most once per tenant per request or job; report an exception, keep the Solutions the tenant already
 *   runs running (fail open: what it turned on, and what the last answer it had granted, for a bounded time) and refuse
 *   to turn one on (fail closed).
 * - MAY: skip the question when no Solution is installed; keep the grants of the last answer per tenant for a bounded
 *   time, only to run them while the operator cannot answer.
 * - MUST NOT: write a deactivation, a journal entry or any other tenant state because of the answer; the kept grants
 *   are a cache of the answer, never used while the operator answers.
 *
 * Why: what a tenant is entitled to is the operator's knowledge (plan, add-ons, bundles); what it turned on and its
 * data are the tenant's.
 */
interface SolutionEntitlementInterface
{
    /**
     * @param  string  $tenantId  tenant ULID in its lowercase RFC 4122 form
     */
    public function entitlementFor(string $tenantId): SolutionEntitlement;
}
