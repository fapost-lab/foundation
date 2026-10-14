<?php

declare(strict_types=1);

namespace Fapost\Foundation\Console\DTO;

use Fapost\Foundation\Console\Internal\DeclarationFormat;
use InvalidArgumentException;

/**
 * A Solution's own application in the console's app rail. At most one per Solution; its id is the Solution id.
 *
 * @experimental
 */
final readonly class ConsoleApplication
{
    /**
     * @param  string  $label  translation key in the Solution's namespace, such as "feedback::console.app"
     * @param  string  $icon  lucide name, kebab case
     * @param  string  $entryRoute  route name, "solutions.<id>.*"; where the rail opens the application
     * @param  list<MenuItem>  $menu  in display order
     * @param  bool  $perAssistant  the application lives on an assistant, like the Console, rather than on the tenant
     * @param  string|null  $ability  Gate ability to see and open the application
     * @param  string|null  $shortLabel  translation key for the rail; defaults to $label
     *
     * @throws InvalidArgumentException when a name does not have its format or the menu is not a list of MenuItem
     */
    public function __construct(
        public string $label,
        public string $icon,
        public string $entryRoute,
        public array $menu = [],
        public bool $perAssistant = false,
        public ?string $ability = null,
        public ?string $shortLabel = null,
    ) {
        $what = sprintf('Application "%s"', $label);

        DeclarationFormat::label($what, $label);
        DeclarationFormat::icon($what, $icon);
        DeclarationFormat::route($what, $entryRoute);
        DeclarationFormat::ability($what, $ability);

        if (null !== $shortLabel) {
            DeclarationFormat::label($what . ' short', $shortLabel);
        }

        if (!array_is_list($menu)) {
            throw new InvalidArgumentException(sprintf('%s menu must be a list in display order.', $what));
        }

        foreach ($menu as $item) {
            if (!$item instanceof MenuItem) {
                throw new InvalidArgumentException(sprintf('%s menu must hold only %s.', $what, MenuItem::class));
            }
        }
    }
}
