<?php

declare(strict_types=1);

namespace Fapost\Foundation\Settings\DTO;

use Fapost\Foundation\Settings\Enums\PlatformSettingType;
use InvalidArgumentException;

/**
 * One platform-wide setting as Core describes it: what an admin screen needs to render and check its
 * field. Returned by {@see \Fapost\Foundation\Settings\Contracts\PlatformSettingsInterface::definitions()}.
 *
 * `default` is the value the installation uses while nobody stored one; it is already resolved (an
 * `.env` value included), so it may differ between installations. It has the setting's type but may lie
 * outside the bounds: they apply to values someone stores, and an installation keeps a default it chose.
 *
 * @experimental
 */
final readonly class PlatformSettingDefinition
{
    /**
     * @param  string  $key  stable dotted identifier, such as "flow.execution.max_iterations"
     * @param  int|bool|string  $default  the value in effect while none is stored; of the setting's type
     * @param  string  $label  what an operator reads, in English
     * @param  string  $description  what the setting changes, in English
     * @param  string  $group  the section it is shown in, in English, such as "Flow"
     * @param  string|null  $unit  what one unit is, in English, such as "days"; null when it has none
     * @param  int|null  $min  for Integer: the smallest value accepted; for Text: unused
     * @param  int|null  $max  for Integer: the largest value accepted; for Text: the longest text
     * @param  list<string>  $choices  for Choice: the accepted values; empty otherwise
     * @param  bool  $hostModeOnly  true when the setting only has an effect with host resolution
     *
     * @throws InvalidArgumentException when the key, labels, bounds or choices do not fit, or the default is of another type
     */
    public function __construct(
        public string $key,
        public PlatformSettingType $type,
        public int|bool|string $default,
        public string $label,
        public string $description,
        public string $group,
        public ?string $unit = null,
        public ?int $min = null,
        public ?int $max = null,
        public array $choices = [],
        public bool $hostModeOnly = false,
    ) {
        if (1 !== preg_match('/^[a-z][a-z0-9_]*(\.[a-z][a-z0-9_]*)+$/', $key)) {
            throw new InvalidArgumentException(sprintf('Platform setting key "%s" must be dotted snake_case, such as "flow.execution.max_iterations".', $key));
        }

        if ('' === mb_trim($label) || '' === mb_trim($description) || '' === mb_trim($group)) {
            throw new InvalidArgumentException(sprintf('Platform setting "%s" needs a label, a description and a group.', $key));
        }

        if (null !== $min && null !== $max && $min > $max) {
            throw new InvalidArgumentException(sprintf('Platform setting "%s" has a minimum above its maximum.', $key));
        }

        if (PlatformSettingType::Choice === $type && [] === $choices) {
            throw new InvalidArgumentException(sprintf('Platform setting "%s" is a choice without choices.', $key));
        }

        if (PlatformSettingType::Choice !== $type && [] !== $choices) {
            throw new InvalidArgumentException(sprintf('Platform setting "%s" has choices but is not a choice.', $key));
        }

        if (null !== $this->typeViolation($default)) {
            throw new InvalidArgumentException(sprintf('Platform setting "%s" has a default of another type: it %s', $key, $this->typeViolation($default)));
        }
    }

    /**
     * Why the value is not accepted for this setting, in English; null when it is.
     */
    public function violation(mixed $value): ?string
    {
        if (null !== ($wrongType = $this->typeViolation($value))) {
            return $wrongType;
        }

        return match ($this->type) {
            PlatformSettingType::Integer => $this->integerViolation($value),
            PlatformSettingType::Boolean => null,
            PlatformSettingType::Text    => $this->textViolation($value),
            PlatformSettingType::Choice  => in_array($value, $this->choices, true)
                ? null
                : sprintf('must be one of: %s.', implode(', ', $this->choices)),
        };
    }

    private function typeViolation(mixed $value): ?string
    {
        return match ($this->type) {
            PlatformSettingType::Integer => is_int($value) ? null : 'must be a whole number.',
            PlatformSettingType::Boolean => is_bool($value) ? null : 'must be true or false.',
            PlatformSettingType::Text,
            PlatformSettingType::Choice => is_string($value) ? null : 'must be text.',
        };
    }

    private function integerViolation(int $value): ?string
    {
        if (null !== $this->min && $value < $this->min) {
            return sprintf('must be at least %d.', $this->min);
        }

        if (null !== $this->max && $value > $this->max) {
            return sprintf('must be at most %d.', $this->max);
        }

        return null;
    }

    private function textViolation(string $value): ?string
    {
        if (null !== $this->max && mb_strlen($value) > $this->max) {
            return sprintf('must be at most %d characters.', $this->max);
        }

        return null;
    }
}
