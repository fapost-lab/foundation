<?php

declare(strict_types=1);

namespace Fapost\Foundation\Channel\Settings;

use InvalidArgumentException;

/**
 * What a channel type lets an operator configure, as the integration declares it.
 *
 * @experimental
 */
final readonly class ChannelSettingsSchema
{
    /**
     * @param  list<ChannelSettingField>  $fields  the settings kept in `channels.config`, in the order a form shows them
     * @param  bool  $usesSecretToken  false when the provider has no webhook secret (Viber signs with the token itself); the form then neither shows nor requires one
     * @param  string|null  $secretTokenPattern  PCRE without delimiters the secret token must match; null for any
     *
     * @throws InvalidArgumentException when a key repeats, a field is not a field or the pattern does not compile
     */
    public function __construct(
        public array $fields = [],
        public bool $usesSecretToken = true,
        public ?string $secretTokenPattern = null,
    ) {
        $seen = [];

        foreach ($fields as $field) {
            if (! $field instanceof ChannelSettingField) {
                throw new InvalidArgumentException('A channel settings schema takes ChannelSettingField objects only.');
            }

            if (isset($seen[$field->key])) {
                throw new InvalidArgumentException(sprintf('Channel setting "%s" is declared twice.', $field->key));
            }

            $seen[$field->key] = true;
        }

        if (null !== $secretTokenPattern) {
            if (! $usesSecretToken) {
                throw new InvalidArgumentException('A secret token pattern needs a channel that uses a secret token.');
            }

            if (false === @preg_match('~' . str_replace('~', '\~', $secretTokenPattern) . '~', '')) {
                throw new InvalidArgumentException('The secret token pattern is not a valid regular expression.');
            }
        }
    }

    public function field(string $key): ?ChannelSettingField
    {
        foreach ($this->fields as $field) {
            if ($field->key === $key) {
                return $field;
            }
        }

        return null;
    }

    /**
     * The fields the form shows and accepts.
     *
     * @return list<ChannelSettingField>
     */
    public function editable(): array
    {
        return array_values(array_filter($this->fields, static fn (ChannelSettingField $field): bool => ! $field->managed));
    }

    /**
     * The keys only the integration writes.
     *
     * @return list<string>
     */
    public function managedKeys(): array
    {
        return array_values(array_map(
            static fn (ChannelSettingField $field): string => $field->key,
            array_filter($this->fields, static fn (ChannelSettingField $field): bool => $field->managed),
        ));
    }
}
