<?php

declare(strict_types=1);

namespace Fapost\Foundation\Credentials\DTO;

use Fapost\Foundation\Credentials\Enums\CredentialFieldKind;
use InvalidArgumentException;

/**
 * One field a credential type declares through
 * {@see \Fapost\Foundation\Credentials\Contracts\CredentialTypeInterface::fields()}: what the console renders and
 * checks when an admin creates or edits a credential of that type.
 *
 * A secret field is stored encrypted, is never sent back to a browser and reaches code only through
 * {@see CredentialSecret::value()}; a field that is not secret is a setting ({@see CredentialSecret::setting()}).
 *
 * @experimental
 */
final readonly class CredentialField
{
    /**
     * @param  string  $key  snake_case identifier, unique within the type, such as "client_secret"
     * @param  string  $label  what an admin reads: English text, or a translation key the implementer resolves
     * @param  bool  $secret  true when the value is a secret: encrypted at rest and never shown again
     * @param  bool  $required  true when a credential of the type cannot be saved without it
     * @param  list<string>  $choices  for Select: the accepted values; empty otherwise
     *
     * @throws InvalidArgumentException when the key, label, kind or choices do not fit
     */
    public function __construct(
        public string $key,
        public string $label,
        public bool $secret = false,
        public bool $required = true,
        public CredentialFieldKind $kind = CredentialFieldKind::Text,
        public array $choices = [],
    ) {
        if (1 !== preg_match('/^[a-z][a-z0-9_]*$/', $key)) {
            throw new InvalidArgumentException(sprintf('Credential field key "%s" must be snake_case, such as "client_secret".', $key));
        }

        if ('' === mb_trim($label)) {
            throw new InvalidArgumentException(sprintf('Credential field "%s" needs a label.', $key));
        }

        if ($secret && CredentialFieldKind::Text !== $kind) {
            throw new InvalidArgumentException(sprintf('Credential field "%s" is secret, so it must be text.', $key));
        }

        if (CredentialFieldKind::Select === $kind && [] === $choices) {
            throw new InvalidArgumentException(sprintf('Credential field "%s" is a select without choices.', $key));
        }

        if (CredentialFieldKind::Select !== $kind && [] !== $choices) {
            throw new InvalidArgumentException(sprintf('Credential field "%s" has choices but is not a select.', $key));
        }

        if ($choices !== array_values(array_filter($choices, is_string(...)))) {
            throw new InvalidArgumentException(sprintf('Credential field "%s" must list its choices as strings.', $key));
        }
    }
}
