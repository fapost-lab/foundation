<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tenancy\Exceptions;

use Fapost\Foundation\Tenancy\Contracts\TenantDecommissionerInterface;
use Fapost\Foundation\Tenancy\Enums\DecommissionFailure;
use RuntimeException;
use Throwable;

/**
 * Thrown by {@see TenantDecommissionerInterface::decommission()} when the tenant was not removed completely.
 *
 * The top-level message never contains internal details. Messages in the `previous` chain come from Core and are
 * not redacted. Repeating the call after `Failed` is safe: the removal picks up what is left.
 *
 * @experimental
 */
final class TenantDecommissionFailedException extends RuntimeException
{
    private function __construct(
        public readonly DecommissionFailure $reason,
        public readonly string $tenantId,
        string $message,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public static function pending(string $tenantId): self
    {
        return new self(DecommissionFailure::Pending, $tenantId, sprintf('Tenant "%s" is pending and cannot be decommissioned.', $tenantId));
    }

    public static function failed(string $tenantId, ?Throwable $previous = null): self
    {
        return new self(DecommissionFailure::Failed, $tenantId, sprintf('Decommissioning tenant "%s" failed.', $tenantId), $previous);
    }
}
