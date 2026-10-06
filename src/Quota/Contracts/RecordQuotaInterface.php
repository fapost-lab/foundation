<?php

declare(strict_types=1);

namespace Fapost\Foundation\Quota\Contracts;

use Fapost\Foundation\Quota\Exceptions\RecordLimitReachedException;
use LogicException;

/**
 * Asks whether the current tenant may create one more record counted by a limit. Implemented by
 * Core; Core and Solutions call it on every path that creates such a record.
 *
 * The caller counts what the tenant has (its records live in the tenant's own storage) and passes
 * that number; the limit comes from {@see TenantLimitsInterface}. Works in the tenant context of
 * the current request or job.
 */
interface RecordQuotaInterface
{
    /**
     * @param  string  $key  a key registered in {@see LimitRegistryInterface}
     * @param  int  $current  how many such records the tenant has now
     *
     * @throws LogicException when the key is not registered
     */
    public function canCreate(string $key, int $current): bool;

    /**
     * @throws RecordLimitReachedException when one more record would exceed the limit
     * @throws LogicException when the key is not registered
     */
    public function assertCanCreate(string $key, int $current): void;
}
