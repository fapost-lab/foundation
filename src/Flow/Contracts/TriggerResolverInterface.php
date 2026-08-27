<?php

declare(strict_types=1);

namespace Fapost\Foundation\Flow\Contracts;

use Fapost\Foundation\Flow\DTO\ResolvedTrigger;
use Fapost\Foundation\Flow\DTO\TriggerContext;

interface TriggerResolverInterface
{
    /**
     * Pure lookup-only boundary: resolver must only match existing triggers and return immutable resolution DTO.
     * No side effects are allowed here (no session creation, flow start, DB writes, audit writes, or runtime mutation).
     */
    public function resolve(TriggerContext $context): ?ResolvedTrigger;
}
