<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tenancy\DTO;

/**
 * Request to create a tenant together with its first administrator.
 *
 * Carries a password hash, never a plain password, so the request can safely sit in a queued
 * job payload or a pending row until provisioning runs.
 */
final readonly class ProvisionTenant
{
    public function __construct(
        public string $slug,
        public string $adminEmail,
        public string $adminName,
        /** A hash produced by the application's hasher (Hash::make); never a plain password. */
        public string $adminPasswordHash,
    ) {}
}
