<?php

declare(strict_types=1);

namespace Fapost\Foundation\Settings\Contracts;

use Fapost\Foundation\Settings\DTO\PlatformSettingDefinition;
use Fapost\Foundation\Settings\DTO\PlatformSettingValue;
use Fapost\Foundation\Settings\Exceptions\InvalidPlatformSettingsException;

/**
 * Core's platform-wide settings: values that apply to the whole installation rather than to one
 * tenant, such as the flow iteration cap. Implemented by Core, which owns their storage and checks;
 * called by an operator package to show and change them in its own panel.
 *
 * A caller renders its fields from {@see self::definitions()}, never from a list of keys of its own,
 * passes null to reset a setting to its default, never reads or writes Core's tables or config for
 * these values, and does not keep {@see self::values()} beyond one request.
 *
 * @experimental
 */
interface PlatformSettingsInterface
{
    /**
     * @return list<PlatformSettingDefinition> every platform setting, ordered by group then key
     */
    public function definitions(): array;

    /**
     * @return list<PlatformSettingValue> the value in effect of every setting, in the order of {@see self::definitions()}
     */
    public function values(): array;

    /**
     * Stores the given values; a null removes the stored value so the default applies again. Keys
     * left out are not changed. Every change is recorded with the actor.
     *
     * @param  array<string, int|bool|string|null>  $changes  key => new value
     * @param  string  $actor  who made the change, such as "operator:<id>"
     *
     * @throws InvalidPlatformSettingsException when a key is unknown or a value is refused; nothing was written
     */
    public function update(array $changes, string $actor): void;
}
