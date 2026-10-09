<?php

declare(strict_types=1);

namespace Fapost\Foundation\Quota\Contracts;

use Fapost\Foundation\Quota\DTO\UsageDecision;
use Fapost\Foundation\Quota\DTO\UsageUnit;

/**
 * Records the use of a per-period limit and says whether it fits. Implemented by an operator
 * package; Core's default allows everything and records nothing.
 *
 * It serves limits registered with {@see \Fapost\Foundation\Quota\Enums\LimitKind::PerPeriod}, such
 * as monthly active contacts or outbound messages. The period belongs to the operator's
 * subscription; Core never computes it and only passes when a unit was used.
 *
 * Implementation (operator package)
 * - MAY: read and write its own tables; cache "already counted this period" per tenant; overshoot
 *   the limit by at most the number of parallel workers.
 * - MUST: be idempotent by (tenant, key, unitKey, period containing occurredAt) so a retried unit
 *   is counted once; never record a refused unit; answer allowed for a key it has no limit for;
 *   derive the period itself from occurredAt; stay cheap, a cache hit or one indexed write.
 * - MUST NOT: throw to refuse, a refusal is a result; call Core's classes or write Core's tables
 *   or a tenant schema; dispatch jobs, send mail or call external services on the calling path;
 *   keep any tenant's data in object properties between calls (workers are long-lived and serve
 *   many tenants).
 *
 * Caller (Core, later Solutions)
 * - MUST: call before the work is done; pass a unitKey and an occurredAt that are the same on
 *   every retry of the same unit where the caller has a stable time, otherwise the current time
 *   (accepting one extra unit for a retry that crosses a period boundary); pass only keys
 *   registered with kind PerPeriod.
 * - MUST NOT: put personal identifiers in unitKey (hash them); call twice with different unitKeys
 *   for one unit.
 *
 * Why: the operator counts in its own storage, which shares no transaction with the tenant's
 * schema, so retries are made safe by the key and not by a transaction; a throw on the hot path
 * would break every bot of every tenant.
 */
interface UsageMeterInterface
{
    /**
     * Counts one unit of use for the tenant, unless the limit is used up.
     *
     * An already counted unit is allowed without counting it again. A refused unit is not
     * recorded, so asking again succeeds once the limit is raised or a new period starts.
     */
    public function consume(UsageUnit $unit): UsageDecision;
}
