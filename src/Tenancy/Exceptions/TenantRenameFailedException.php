<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tenancy\Exceptions;

use Fapost\Foundation\Tenancy\Contracts\TenantRenamerInterface;
use Fapost\Foundation\Tenancy\Enums\RenameFailure;
use Fapost\Foundation\Tenancy\Enums\SlugProblem;
use RuntimeException;
use Throwable;

/**
 * Thrown by {@see TenantRenamerInterface::rename()} when the slug was not changed.
 *
 * The top-level message never contains internal details. Messages in the `previous` chain come from
 * Core and are not redacted.
 */
final class TenantRenameFailedException extends RuntimeException
{
    private function __construct(
        public readonly RenameFailure $reason,
        public readonly string $tenantId,
        string $message,
        ?Throwable $previous = null,
        /** Set only for `SlugInvalid`. */
        public readonly ?SlugProblem $problem = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public static function slugInvalid(string $tenantId, string $slug, ?SlugProblem $problem = null, ?Throwable $previous = null): self
    {
        return new self(RenameFailure::SlugInvalid, $tenantId, sprintf('Tenant slug "%s" is not valid.', $slug), $previous, $problem);
    }

    public static function slugReserved(string $tenantId, string $slug, ?Throwable $previous = null): self
    {
        return new self(RenameFailure::SlugReserved, $tenantId, sprintf('Tenant slug "%s" is reserved.', $slug), $previous);
    }

    public static function slugTaken(string $tenantId, string $slug, ?Throwable $previous = null): self
    {
        return new self(RenameFailure::SlugTaken, $tenantId, sprintf('Tenant slug "%s" is already taken.', $slug), $previous);
    }

    public static function tenantNotFound(string $tenantId, ?Throwable $previous = null): self
    {
        return new self(RenameFailure::TenantNotFound, $tenantId, sprintf('Tenant "%s" was not found.', $tenantId), $previous);
    }

    public static function tenantPending(string $tenantId, ?Throwable $previous = null): self
    {
        return new self(RenameFailure::TenantPending, $tenantId, sprintf('Tenant "%s" is pending and cannot be renamed.', $tenantId), $previous);
    }

    public static function unavailable(string $tenantId, ?Throwable $previous = null): self
    {
        return new self(RenameFailure::Unavailable, $tenantId, 'Tenants cannot be renamed unless the installation resolves them by host.', $previous);
    }

    public static function failed(string $tenantId, ?Throwable $previous = null): self
    {
        return new self(RenameFailure::Failed, $tenantId, sprintf('Renaming tenant "%s" failed.', $tenantId), $previous);
    }
}
