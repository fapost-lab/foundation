<?php

declare(strict_types=1);

namespace Fapost\Foundation\Credentials\Exceptions;

use RuntimeException;

/**
 * Thrown by {@see \Fapost\Foundation\Credentials\Contracts\CredentialStoreInterface::secret()} when the current
 * tenant has no credential with the id — including when another tenant has one, so the answer never tells
 * whether an id exists elsewhere.
 *
 * @experimental
 */
final class CredentialNotFoundException extends RuntimeException
{
    private function __construct(
        public readonly string $credentialId,
        string $message,
    ) {
        parent::__construct($message);
    }

    public static function forId(string $credentialId): self
    {
        return new self($credentialId, sprintf('Credential "%s" was not found.', $credentialId));
    }
}
