<?php

declare(strict_types=1);

namespace Fapost\Foundation\Console\DTO;

use Fapost\Foundation\Console\Enums\SettingsTarget;
use Fapost\Foundation\Console\Internal\DeclarationFormat;
use InvalidArgumentException;

/**
 * A tab a Solution adds to a settings screen.
 *
 * @experimental
 */
final readonly class SettingsTab
{
    /**
     * @param  string  $key  unique per Solution and settings screen
     * @param  string  $label  translation key in the Solution's namespace
     * @param  string  $component  "<id>::<Path>", listed under `components` in the Solution's `fapost-ui.json`
     * @param  string|null  $ability  Gate ability to see the tab
     * @param  int  $order  lower first; Core's own tabs come before every Solution's
     *
     * @throws InvalidArgumentException when a name does not have its format
     */
    public function __construct(
        public SettingsTarget $target,
        public string $key,
        public string $label,
        public string $component,
        public ?string $ability = null,
        public int $order = 0,
    ) {
        $what = sprintf('Settings tab "%s"', $key);

        DeclarationFormat::key('Settings tab', $key);
        DeclarationFormat::label($what, $label);
        DeclarationFormat::component($what, $component);
        DeclarationFormat::ability($what, $ability);
    }
}
