<?php

declare(strict_types=1);

namespace FAPost\Foundation\Flow\Contracts;

use FAPost\Foundation\Flow\DTO\ResolvedTrigger;
use FAPost\Foundation\Flow\DTO\TriggerContext;

interface TriggerResolverInterface
{
    /**
     * Pure lookup-only boundary: resolver must only match existing triggers and return immutable resolution DTO.
     * No side effects are allowed here (no session creation, flow start, DB writes, audit writes, or runtime mutation).
     */
    public function resolve(TriggerContext $context): ?ResolvedTrigger;
}
