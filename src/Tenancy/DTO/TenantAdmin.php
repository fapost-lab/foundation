<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tenancy\DTO;

/**
 * The first administrator of a tenant that is being provisioned.
 *
 * Carries a password hash, never a plain password, so it can safely sit in a queued job payload.
 */
final readonly class TenantAdmin
{
    public function __construct(
        public string $email,
        /** An empty name becomes `Administrator`. */
        public string $name,
        /** A hash produced by the application's hasher (Hash::make); never a plain password. */
        public string $passwordHash,
    ) {
    }
}
