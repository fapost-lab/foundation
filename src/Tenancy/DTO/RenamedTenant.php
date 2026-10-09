<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tenancy\DTO;

use DateTimeImmutable;
use Fapost\Foundation\Tenancy\Contracts\TenantRenamerInterface;

/**
 * The outcome of {@see TenantRenamerInterface::rename()}.
 */
final readonly class RenamedTenant
{
    public function __construct(
        /** Tenant ULID in its lowercase RFC 4122 form. */
        public string $id,
        /** The slug the tenant has now. */
        public string $slug,
        /** The slug before the call; equal to {@see self::$slug} when {@see self::$changed} is false. */
        public string $previousSlug,
        /** False when the tenant already had the requested slug and nothing was written. */
        public bool $changed,
        /** Absolute URL of the tenant's new host root, with a trailing slash, as in TenantSummary. Built by Core. */
        public string $url,
        /** Absolute URL of the admin panel login on the new host. Built by Core. */
        public string $loginUrl,
        /**
         * UTC; until when the previous host redirects GET and HEAD requests to the new one. Null when
         * nothing was changed or the redirect is switched off by configuration. The previous slug stays
         * reserved for this tenant either way.
         */
        public ?DateTimeImmutable $redirectUntil = null,
    ) {
    }
}
