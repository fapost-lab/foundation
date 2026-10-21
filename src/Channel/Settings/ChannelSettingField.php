<?php

declare(strict_types=1);

namespace Fapost\Foundation\Channel\Settings;

use InvalidArgumentException;

/**
 * One setting of a channel type, stored under `key` in the channel's `config`.
 *
 * Core validates and renders the value from this declaration; the integration never validates it. The
 * declaration itself is checked here, so a contradictory field fails when the integration is registered.
 *
 * A field is never secret: `config` is plain JSON that reaches queued jobs. Secrets of a channel live in
 * its encrypted token and secret-token columns.
 *
 * @experimental
 */
final readonly class ChannelSettingField
{
    /**
     * @param  string  $key  `^[a-z][a-z0-9_]{0,63}$`, the key in `channels.config`
     * @param  string  $label  what an operator reads, in English
     * @param  string|null  $help  one line under the control, in English
     * @param  int|bool|string|list<string>|null  $default  the value in effect while none is stored; of the field's type, null for none
     * @param  bool  $required  for Choices: at least one value; for the rest: a value must be given
     * @param  int|null  $min  for Integer: the smallest value
     * @param  int|null  $max  for Integer: the largest value; for Text: the longest text
     * @param  array<string, string>  $choices  for Choice and Choices: accepted value => English label; empty otherwise
     * @param  bool  $managed  the integration writes it (a bot name read from the provider), not the form; never shown or accepted from a request, kept when the form is saved
     * @param  string|null  $translationKey  prefix of the translations: `{prefix}.label`, `{prefix}.help`, `{prefix}.choices.{value}`; the English text above is used when a translation is missing
     *
     * @throws InvalidArgumentException when the key, label, bounds, choices or default do not fit the type
     */
    public function __construct(
        public string $key,
        public ChannelSettingType $type,
        public string $label,
        public ?string $help = null,
        public int|bool|string|array|null $default = null,
        public bool $required = false,
        public ?int $min = null,
        public ?int $max = null,
        public array $choices = [],
        public bool $managed = false,
        public ?string $translationKey = null,
    ) {
        if (1 !== preg_match('/^[a-z][a-z0-9_]{0,63}$/', $key)) {
            throw new InvalidArgumentException(sprintf('Channel setting key "%s" must be snake_case, up to 64 characters.', $key));
        }

        if ('' === mb_trim($label)) {
            throw new InvalidArgumentException(sprintf('Channel setting "%s" needs a label.', $key));
        }

        if (null !== $min && null !== $max && $min > $max) {
            throw new InvalidArgumentException(sprintf('Channel setting "%s" has a minimum above its maximum.', $key));
        }

        if ((null !== $min) && ChannelSettingType::Integer !== $type) {
            throw new InvalidArgumentException(sprintf('Channel setting "%s" has a minimum, which only an integer takes.', $key));
        }

        if (null !== $max && ! in_array($type, [ChannelSettingType::Integer, ChannelSettingType::Text], true)) {
            throw new InvalidArgumentException(sprintf('Channel setting "%s" has a maximum, which only an integer or a text takes.', $key));
        }

        $takesChoices = in_array($type, [ChannelSettingType::Choice, ChannelSettingType::Choices], true);

        if (! $takesChoices && [] !== $choices) {
            throw new InvalidArgumentException(sprintf('Channel setting "%s" lists choices, which only a choice or choices field takes.', $key));
        }

        if ($takesChoices && [] === $choices) {
            throw new InvalidArgumentException(sprintf('Channel setting "%s" needs its choices.', $key));
        }

        foreach ($choices as $value => $choiceLabel) {
            if ('' === (string) $value || ! is_string($choiceLabel) || '' === mb_trim($choiceLabel)) {
                throw new InvalidArgumentException(sprintf('Channel setting "%s" has a choice without a value or a label.', $key));
            }
        }

        if ($managed && $required) {
            throw new InvalidArgumentException(sprintf('Channel setting "%s" is managed by the integration, so it cannot be required of the form.', $key));
        }

        if (null !== $translationKey && '' === mb_trim($translationKey)) {
            throw new InvalidArgumentException(sprintf('Channel setting "%s" has an empty translation key.', $key));
        }

        if (null !== $default && ! $this->defaultFits($default)) {
            throw new InvalidArgumentException(sprintf('Channel setting "%s" has a default that does not fit its type.', $key));
        }
    }

    /**
     * @param  int|bool|string|list<string>  $default
     */
    private function defaultFits(int|bool|string|array $default): bool
    {
        $accepted = array_map(strval(...), array_keys($this->choices));

        return match ($this->type) {
            ChannelSettingType::Text    => is_string($default) && (null === $this->max || mb_strlen($default) <= $this->max),
            ChannelSettingType::Integer => is_int($default)
                && (null === $this->min || $default >= $this->min)
                && (null === $this->max || $default <= $this->max),
            ChannelSettingType::Boolean => is_bool($default),
            ChannelSettingType::Choice  => is_string($default) && in_array($default, $accepted, true),
            ChannelSettingType::Choices => is_array($default)
                && array_is_list($default)
                && [] === array_diff($default, $accepted)
                && count($default) === count(array_unique($default)),
        };
    }
}
