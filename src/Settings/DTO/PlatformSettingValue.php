<?php

declare(strict_types=1);

namespace Fapost\Foundation\Settings\DTO;

/**
 * What a platform setting is now. Returned by
 * {@see \Fapost\Foundation\Settings\Contracts\PlatformSettingsInterface::values()}.
 *
 * @experimental
 */
final readonly class PlatformSettingValue
{
    /**
     * @param  int|bool|string  $value  the value in effect: the stored one, or else the default
     * @param  int|bool|string  $default  the value in effect while none is stored
     * @param  bool  $stored  whether someone stored a value, as opposed to the default applying
     */
    public function __construct(
        public string $key,
        public int|bool|string $value,
        public int|bool|string $default,
        public bool $stored,
    ) {
    }
}
