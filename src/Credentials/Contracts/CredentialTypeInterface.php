<?php

declare(strict_types=1);

namespace Fapost\Foundation\Credentials\Contracts;

use Fapost\Foundation\Credentials\DTO\CredentialField;

/**
 * A kind of credential: the fields an admin fills in and, through a sub-interface, how it is applied. Types
 * hold no state; Core builds one when it needs it.
 *
 * @experimental
 */
interface CredentialTypeInterface
{
    /**
     * Stable snake_case id, stored with every credential of the type, such as "bearer".
     */
    public function id(): string;

    /**
     * What an admin reads: English text, or a translation key the implementer resolves.
     */
    public function label(): string;

    /**
     * The fields of the type, in the order the form shows them.
     *
     * @return list<CredentialField>
     */
    public function fields(): array;
}
