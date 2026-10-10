<?php

declare(strict_types=1);

namespace Fapost\Foundation\Quota\Contracts;

use Fapost\Foundation\Quota\DTO\LimitNotice;
use Fapost\Foundation\Quota\Enums\LimitNoticeReason;

/**
 * Supplies the commercial "what to do next" wording of the notification Core sends a tenant's
 * admins when a limit refuses work or a record limit has just been filled (see
 * {@see LimitNoticeReason}). Implemented by an operator package; Core's default returns
 * null, and Core then writes its own text.
 *
 * Core owns the facts of the refusal (the limit's name, "used of limit", what was refused) and
 * the delivery (recipients, channels, language, de-duplication to one notice per limit and
 * period). The notice is sent when work is refused ({@see LimitNoticeReason::Refused}) and, for a
 * Records limit, when a saved record fills it ({@see LimitNoticeReason::Reached}), so the wording
 * can differ: "upgrade to create more" against "you can no longer create". The operator owns only the part that replaces "contact the platform administrator":
 * an upgrade offer, a plan name, a button.
 *
 * Implementation (operator package)
 * - MAY: read its own tables (plan, subscription); answer in the requested locale, or in English
 *   when it has no translation; word the notice by the reason; return null to let Core write its own text.
 * - MUST: return a notice that replaces only the "what to do next" block; give an absolute
 *   http(s) action URL; stay cheap, Core asks once per distinct recipient locale from a worker.
 * - MUST NOT: repeat the limit's name or the numbers, Core writes them; send mail, dispatch jobs
 *   or call external services; call Core's classes or write Core's tables or a tenant schema;
 *   keep any tenant's data in object properties between calls (workers are long-lived and serve
 *   many tenants).
 *
 * Caller (Core)
 * - MUST: call from a worker, never on the path that refuses the work; treat an exception as
 *   null and report it, a faulty operator never stops the notification.
 * - MUST NOT: call it on the hot path of a gate, or more than once per distinct recipient locale
 *   for one notification.
 *
 * Why: the operator knows what a tenant can buy, Core knows who the tenant's admins are and what
 * language they read; splitting the text keeps both facts with their owner.
 */
interface LimitNoticeInterface
{
    /**
     * The wording for a tenant whose limit refused work or was just filled, as $reason says, in the
     * locale of the recipients.
     *
     * Null when the operator has nothing to add (for this reason or at all); Core then writes its own text.
     */
    public function noticeFor(string $tenantId, string $key, string $locale, LimitNoticeReason $reason): ?LimitNotice;
}
