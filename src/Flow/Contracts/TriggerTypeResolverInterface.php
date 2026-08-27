<?php

declare(strict_types=1);

namespace Fapost\Foundation\Flow\Contracts;

use Fapost\Foundation\Flow\DTO\ResolvedTrigger;
use Fapost\Foundation\Flow\DTO\TriggerContext;

interface TriggerTypeResolverInterface
{
    public function resolve(TriggerContext $context): ?ResolvedTrigger;
}
