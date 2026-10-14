<?php

declare(strict_types=1);

namespace Fapost\Foundation\Console\DTO;

use Fapost\Foundation\Console\Internal\DeclarationFormat;
use InvalidArgumentException;

/**
 * One entry of a Solution application's menu.
 *
 * @experimental
 */
final readonly class MenuItem
{
    /**
     * @param  string  $label  translation key in the Solution's namespace, such as "feedback::console.menu.reviews"
     * @param  string  $route  route name, "solutions.<id>.*"
     * @param  string  $icon  lucide name, kebab case
     * @param  string|null  $ability  Gate ability to see the entry
     * @param  string|null  $group  translation key of the group heading the entry sits under
     *
     * @throws InvalidArgumentException when a name does not have its format
     */
    public function __construct(
        public string $label,
        public string $route,
        public string $icon = 'circle',
        public ?string $ability = null,
        public ?string $group = null,
    ) {
        $what = sprintf('Menu item "%s"', $label);

        DeclarationFormat::label($what, $label);
        DeclarationFormat::route($what, $route);
        DeclarationFormat::icon($what, $icon);
        DeclarationFormat::ability($what, $ability);

        if (null !== $group) {
            DeclarationFormat::label($what . ' group', $group);
        }
    }
}
