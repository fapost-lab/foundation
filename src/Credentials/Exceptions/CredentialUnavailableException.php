<?php

declare(strict_types=1);

namespace Fapost\Foundation\Credentials\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Thrown by {@see \Fapost\Foundation\Credentials\Contracts\HttpCredentialTypeInterface::authorize()} when the
 * credential exists but cannot be applied now: an OAuth2 token endpoint refused it or did not answer, or a
 * field the type needs is missing.
 *
 * The message is written for a log and never carries a secret value, a token or a response body; the
 * previous exception, when given, must not either.
 *
 * @experimental
 */
final class CredentialUnavailableException extends RuntimeException
{
    private function __construct(
        public readonly string $credentialId,
        string $message,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    /**
     * @param  string  $reason  why, in English, without any secret, token or response body
     */
    public static function because(string $credentialId, string $reason, ?Throwable $previous = null): self
    {
        return new self($credentialId, sprintf('Credential "%s" is unavailable: %s', $credentialId, $reason), $previous);
    }
}
