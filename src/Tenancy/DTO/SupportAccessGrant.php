<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tenancy\DTO;

use DateTimeImmutable;
use JsonSerializable;
use SensitiveParameter;

/**
 * A single-use permission to enter a tenant as its platform support user.
 *
 * Send the browser to {@see $enterUrl} with a POST whose body carries {@see token()} as `token`; do not
 * put the token in a URL, a log or a queue. The token is kept out of dumps and JSON.
 */
final readonly class SupportAccessGrant implements JsonSerializable
{
    public function __construct(
        /** Absolute URL on the tenant's host that accepts the POST. Built by Core. */
        public string $enterUrl,
        #[SensitiveParameter]
        private string $token,
        /** UTC; the token is refused after this moment. */
        public DateTimeImmutable $expiresAt,
    ) {
    }

    public function token(): string
    {
        return $this->token;
    }

    /**
     * @return array<string, mixed>
     */
    public function __debugInfo(): array
    {
        return $this->redacted();
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->redacted();
    }

    /**
     * @return array<string, mixed>
     */
    private function redacted(): array
    {
        return ['enterUrl' => $this->enterUrl, 'token' => '[redacted]', 'expiresAt' => $this->expiresAt->format(DATE_ATOM)];
    }
}
