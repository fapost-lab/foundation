<?php

declare(strict_types=1);

namespace Fapost\Foundation\Console\DTO;

use Fapost\Foundation\Console\Enums\ConsoleTarget;
use Fapost\Foundation\Console\Enums\ContextActionKind;
use Fapost\Foundation\Console\Internal\DeclarationFormat;
use InvalidArgumentException;

/**
 * An action a Solution adds to a record's menu in the console. Built with {@see self::link()} or {@see self::dialog()}.
 *
 * @experimental
 */
final readonly class ContextAction
{
    /**
     * @throws InvalidArgumentException when a name does not have its format
     */
    private function __construct(
        public ConsoleTarget $target,
        public string $key,
        public string $label,
        public ContextActionKind $kind,
        public ?string $route,
        public ?string $component,
        public string $icon,
        public ?string $ability,
        public int $order,
    ) {
        $what = sprintf('Context action "%s"', $key);

        DeclarationFormat::key('Context action', $key);
        DeclarationFormat::label($what, $label);
        DeclarationFormat::icon($what, $icon);
        DeclarationFormat::ability($what, $ability);

        if (null !== $route) {
            DeclarationFormat::route($what, $route);
        }

        if (null !== $component) {
            DeclarationFormat::component($what, $component);
        }
    }

    /**
     * An action that navigates to a route of the Solution. The route takes a `record` parameter: Core fills it
     * with the id of the contact or conversation the action was chosen on.
     *
     * @param  string  $key  unique per Solution and target
     * @param  string  $label  translation key in the Solution's namespace
     * @param  string  $route  route name, "solutions.<id>.*"
     * @param  int  $order  lower first; Core's own actions come before every Solution's
     *
     * @throws InvalidArgumentException when a name does not have its format
     *
     * @experimental
     */
    public static function link(
        ConsoleTarget $target,
        string $key,
        string $label,
        string $route,
        string $icon = 'circle',
        ?string $ability = null,
        int $order = 0,
    ): self {
        return new self($target, $key, $label, ContextActionKind::Link, $route, null, $icon, $ability, $order);
    }

    /**
     * An action that opens a component of the Solution in a dialog; the component receives the record.
     *
     * @param  string  $key  unique per Solution and target
     * @param  string  $label  translation key in the Solution's namespace
     * @param  string  $component  "<id>::<Path>", listed under `components` in the Solution's `fapost-ui.json`
     * @param  int  $order  lower first; Core's own actions come before every Solution's
     *
     * @throws InvalidArgumentException when a name does not have its format
     *
     * @experimental
     */
    public static function dialog(
        ConsoleTarget $target,
        string $key,
        string $label,
        string $component,
        string $icon = 'circle',
        ?string $ability = null,
        int $order = 0,
    ): self {
        return new self($target, $key, $label, ContextActionKind::Dialog, null, $component, $icon, $ability, $order);
    }
}
