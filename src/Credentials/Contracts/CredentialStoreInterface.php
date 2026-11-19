<?php

declare(strict_types=1);

namespace Fapost\Foundation\Credentials\Contracts;

use Fapost\Foundation\Credentials\DTO\CredentialReference;
use Fapost\Foundation\Credentials\DTO\CredentialSecret;
use Fapost\Foundation\Credentials\Exceptions\CredentialNotFoundException;

/**
 * The tenant's saved credentials, read-only. Implemented by Core, which also owns writing them (its console).
 *
 * Every call reads in the current tenant and fails when there is none; a credential of another tenant is
 * never returned and is not found. Resolve it for each call rather than keeping it: the binding is scoped to
 * the request or the job.
 *
 * @experimental
 */
interface CredentialStoreInterface
{
    /**
     * The tenant's credentials as a picker lists them, without values, ordered by name.
     *
     * @param  string|null  $type  only credentials of this type id; null for all of them
     *
     * @return list<CredentialReference>
     */
    public function references(?string $type = null): array;

    /**
     * The credential with its decrypted secret values, to apply to one call. Do not keep, log or serialize it.
     *
     * @throws CredentialNotFoundException when the current tenant has no credential with the id
     */
    public function secret(string $id): CredentialSecret;
}
