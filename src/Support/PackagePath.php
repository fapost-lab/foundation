<?php

declare(strict_types=1);

namespace Fapost\Foundation\Support;

/**
 * The rule for a file path a package declares about itself, such as `extra.fapost.ui` or a file listed in
 * `fapost-ui.json`. Shared by the parsers so the rule is written once. Not part of the contract.
 *
 * @internal
 */
final class PackagePath
{
    /** One path segment: letters, digits, ".", "_" and "-", not starting with "." (so no ".", ".." or hidden file). */
    public const string SEGMENT_PATTERN = '/^[A-Za-z0-9_-][A-Za-z0-9._-]*$/D';

    /**
     * A relative path inside the package: "/"-separated segments from the allow-list, none starting with ".",
     * ending with $extension and longer than it.
     */
    public static function isRelativeFile(mixed $path, string $extension): bool
    {
        if (!is_string($path) || !str_ends_with($path, $extension) || mb_strlen($path) <= mb_strlen($extension)) {
            return false;
        }

        foreach (explode('/', $path) as $segment) {
            if (1 !== preg_match(self::SEGMENT_PATTERN, $segment)) {
                return false;
            }
        }

        return true;
    }
}
