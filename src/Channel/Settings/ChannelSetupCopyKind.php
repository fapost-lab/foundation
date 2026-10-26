<?php

declare(strict_types=1);

namespace Fapost\Foundation\Channel\Settings;

/**
 * What a {@see ChannelSetupStep::$copy} value is, so the console can render it as plain text, a link or a code block.
 *
 * @experimental Since foundation 0.33.
 */
enum ChannelSetupCopyKind: string
{
    case Text = 'text';
    case Url  = 'url';
    case Code = 'code';
}
