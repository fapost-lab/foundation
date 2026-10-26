<?php

declare(strict_types=1);

namespace Fapost\Foundation\Channel;

use RuntimeException;

/**
 * Thrown by {@see ChannelInboundAdapterInterface::normalize()} for a delivery that is not a user's message for the
 * flow: the bot's own message, an edit, a system event. Core drops it without a retry, a contact or a transcript.
 *
 * @experimental Since foundation 0.33.
 */
final class InboundEventIgnoredException extends RuntimeException
{
}
