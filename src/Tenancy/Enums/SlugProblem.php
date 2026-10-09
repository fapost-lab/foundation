<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tenancy\Enums;

/**
 * What is wrong with a slug that is `Invalid`.
 *
 * Carries a code, not a sentence: the text shown to a person is the caller's, in the caller's UI
 * language. New values may be added in a minor release; callers keep a default arm.
 */
enum SlugProblem: string
{
    /** Not a DNS label: characters outside lower-case letters, digits and hyphen, or a hyphen at an edge. */
    case Malformed = 'malformed';

    /** Longer than 56 characters. */
    case TooLong = 'too_long';

    /** Starts with the punycode prefix `xn--`. */
    case PunycodePrefix = 'punycode_prefix';

    /** Contains two hyphens in a row. */
    case ConsecutiveHyphens = 'consecutive_hyphens';
}
