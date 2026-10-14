<?php

declare(strict_types=1);

namespace Fapost\Foundation\Console;

/**
 * The name formats of console declarations, as data.
 *
 * Foundation checks the format; that a prefix (`<id>::`, `solutions.<id>.`) belongs to the Solution
 * whose provider is booting is checked by Core's registry.
 *
 * @experimental
 */
final class ConsoleNames
{
    /** A component of a Solution: "<id>::<Path>", Path = PascalCase segments joined by "/", such as "feedback::Widgets/Score". */
    public const string COMPONENT_PATTERN = '/^([a-z][a-z0-9_]{1,31})::([A-Z][A-Za-z0-9]*(\/[A-Z][A-Za-z0-9]*)*)$/D';

    /** The Path part of a component name, without the "<id>::" prefix; keys of `pages` and `components` in `fapost-ui.json`. */
    public const string PATH_PATTERN = '/^[A-Z][A-Za-z0-9]*(\/[A-Z][A-Za-z0-9]*)*$/D';

    /** The key of a declaration, unique per Solution and point, such as "score". */
    public const string KEY_PATTERN = '/^[a-z][a-z0-9_]{0,31}$/D';

    /** A translation key in the Solution's namespace, such as "feedback::console.app". */
    public const string LABEL_PATTERN = '/^[a-z][a-z0-9_]{1,31}::\S+$/D';

    /** A lucide icon name in kebab case, such as "message-circle". */
    public const string ICON_PATTERN = '/^[a-z][a-z0-9]*(-[a-z0-9]+)*$/D';

    /** Route names of a Solution start with this prefix followed by "<id>.". */
    public const string ROUTE_PREFIX = 'solutions.';

    /** A route name of a Solution: "solutions.<id>.<name>", such as "solutions.feedback.index". */
    public const string ROUTE_PATTERN = '/^solutions\.[a-z][a-z0-9_]{1,31}\.[A-Za-z0-9_.\-]*[A-Za-z0-9_]$/D';

    /**
     * The Solution id a component name belongs to.
     *
     * @return string|null  the "<id>" of "<id>::<Path>", or null when the name does not match {@see self::COMPONENT_PATTERN}
     *
     * @experimental
     */
    public static function solutionOf(string $component): ?string
    {
        return 1 === preg_match(self::COMPONENT_PATTERN, $component, $matches) ? $matches[1] : null;
    }
}
