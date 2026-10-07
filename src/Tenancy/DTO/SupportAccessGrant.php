<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tenancy\DTO;

use DateTimeImmutable;
use SensitiveParameter;

/**
 * A single-use permission to enter a tenant as its platform support user.
 *
 * Send the browser to {@see $enterUrl} with a POST whose body carries `token`; do not put the token
 * in a URL, a log or a queue.
 */
final readonly class SupportAccessGrant
{
    public function __construct(
        /** Absolute URL on the tenant's host that accepts the POST. Built by Core. */
        public string $enterUrl,
        #[SensitiveParameter]
        public string $token,
        /** UTC; the token is refused after this moment. */
        public DateTimeImmutable $expiresAt,
    ) {
    }

    /**
     * Keeps the token out of dumps and logged context.
     *
     * @return array<string, mixed>
     */
    public function __debugInfo(): array
    {
        return ['enterUrl' => $this->enterUrl, 'token' => '[redacted]', 'expiresAt' => $this->expiresAt];
    }
}
