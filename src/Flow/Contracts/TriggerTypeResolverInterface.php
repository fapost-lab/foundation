<?php

declare(strict_types=1);

namespace FAPost\Foundation\Flow\Contracts;

use FAPost\Foundation\Flow\DTO\ResolvedTrigger;
use FAPost\Foundation\Flow\DTO\TriggerContext;

interface TriggerTypeResolverInterface
{
    public function resolve(TriggerContext $context): ?ResolvedTrigger;
}
