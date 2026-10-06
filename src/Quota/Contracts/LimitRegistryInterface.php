<?php

declare(strict_types=1);

namespace Fapost\Foundation\Quota\Contracts;

use Fapost\Foundation\Quota\DTO\LimitDefinition;
use LogicException;

/**
 * The limits a platform can enforce. Implemented by Core.
 *
 * Core, and later Solutions, register their limit keys while the application boots; the registry
 * is closed afterwards. Operator packages read it, for example to build a plan form.
 */
interface LimitRegistryInterface
{
    /**
     * @throws LogicException when the key is already registered or the registry is closed
     */
    public function register(LimitDefinition $limit): void;

    public function find(string $key): ?LimitDefinition;

    /**
     * @return list<LimitDefinition> ordered by key
     */
    public function all(): array;
}
