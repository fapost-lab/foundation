<?php

declare(strict_types=1);

namespace Fapost\Foundation\Credentials\DTO;

use InvalidArgumentException;

/**
 * A credential as a picker lists it: what identifies it and what it is, never a value. Returned by
 * {@see \Fapost\Foundation\Credentials\Contracts\CredentialStoreInterface::references()}.
 *
 * @experimental
 */
final readonly class CredentialReference
{
    /**
     * @param  string  $id  the credential's id (a ULID in Core); not a secret
     * @param  string  $name  the name an admin gave it, unique within the tenant
     * @param  string  $type  the id of its credential type, such as "bearer"
     *
     * @throws InvalidArgumentException when a part is empty
     */
    public function __construct(
        public string $id,
        public string $name,
        public string $type,
    ) {
        if ('' === $id || '' === mb_trim($name) || '' === $type) {
            throw new InvalidArgumentException('A credential reference needs an id, a name and a type.');
        }
    }
}
