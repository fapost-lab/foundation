<?php

declare(strict_types=1);

namespace Fapost\Foundation\Channel\Settings;

use InvalidArgumentException;

/**
 * Thrown by {@see ManagedChannelSettingsInterface::report()} for a key that is not a managed field of the channel's
 * schema, or a value that does not fit the field's type. It is the integration's mistake, not the operator's.
 *
 * Since foundation 0.28, experimental.
 */
final class InvalidManagedSettingException extends InvalidArgumentException
{
}
