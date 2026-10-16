<?php

declare(strict_types=1);

namespace Fapost\Foundation\Settings\Enums;

/**
 * The type of a platform setting's value, and so the field that edits it.
 *
 * New types may be added in a minor release: callers matching on it keep a default arm.
 *
 * @experimental
 */
enum PlatformSettingType: string
{
    /** A whole number, optionally between a minimum and a maximum. */
    case Integer = 'integer';

    /** On or off. */
    case Boolean = 'boolean';

    /** A line of text, optionally limited to a maximum length. */
    case Text = 'text';

    /** One of a fixed list of strings. */
    case Choice = 'choice';
}
