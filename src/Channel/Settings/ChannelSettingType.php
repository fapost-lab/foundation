<?php

declare(strict_types=1);

namespace Fapost\Foundation\Channel\Settings;

/**
 * The kind of value a channel setting holds. Core builds its validation rules and its form control from it.
 *
 * @experimental
 */
enum ChannelSettingType: string
{
    /** A string; `max` is its longest length. */
    case Text = 'text';

    /** A whole number between `min` and `max`. */
    case Integer = 'integer';

    case Boolean = 'boolean';

    /** One value out of `choices`. */
    case Choice = 'choice';

    /** A list of values out of `choices`, without repeats. */
    case Choices = 'choices';
}
