<?php

declare(strict_types=1);

namespace Fapost\Foundation\Console\Internal;

use Fapost\Foundation\Console\ConsoleNames;
use InvalidArgumentException;

/**
 * Format checks shared by the console DTOs. Not part of the contract.
 *
 * @internal
 */
final class DeclarationFormat
{
    private const string CLASS_NAME_PATTERN = '/^[A-Za-z_][A-Za-z0-9_]*(\\\\[A-Za-z_][A-Za-z0-9_]*)*$/D';

    /**
     * @throws InvalidArgumentException
     */
    public static function key(string $what, string $key): void
    {
        if (1 !== preg_match(ConsoleNames::KEY_PATTERN, $key)) {
            throw new InvalidArgumentException(sprintf('%s key "%s" must match %s.', $what, $key, ConsoleNames::KEY_PATTERN));
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function label(string $what, string $label): void
    {
        if (1 !== preg_match(ConsoleNames::LABEL_PATTERN, $label)) {
            throw new InvalidArgumentException(sprintf('%s label "%s" must be a translation key in the Solution\'s namespace, such as "feedback::console.app".', $what, $label));
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function icon(string $what, string $icon): void
    {
        if (1 !== preg_match(ConsoleNames::ICON_PATTERN, $icon)) {
            throw new InvalidArgumentException(sprintf('%s icon "%s" must be a lucide icon name in kebab case.', $what, $icon));
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function route(string $what, string $route): void
    {
        if (1 !== preg_match(ConsoleNames::ROUTE_PATTERN, $route)) {
            throw new InvalidArgumentException(sprintf('%s route "%s" must be a route name of the form "%s<id>.<name>".', $what, $route, ConsoleNames::ROUTE_PREFIX));
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function component(string $what, string $component): void
    {
        if (null === ConsoleNames::solutionOf($component)) {
            throw new InvalidArgumentException(sprintf('%s component "%s" must be "<id>::<Path>" with PascalCase path segments.', $what, $component));
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function ability(string $what, ?string $ability): void
    {
        if (null !== $ability && ('' === $ability || mb_trim($ability) !== $ability)) {
            throw new InvalidArgumentException(sprintf('%s ability must be a non-empty Gate ability name without surrounding spaces.', $what));
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function className(string $what, string $class): void
    {
        if (1 !== preg_match(self::CLASS_NAME_PATTERN, $class)) {
            throw new InvalidArgumentException(sprintf('%s class "%s" must be a fully qualified class name without a leading backslash.', $what, $class));
        }
    }
}
