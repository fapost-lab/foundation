<?php

declare(strict_types=1);

namespace Fapost\Foundation\Messaging;

use InvalidArgumentException;

/**
 * How many buttons a channel shows in one message and how long their labels may be, declared beside the
 * {@see ChannelCapability} flags in {@see ChannelCapabilities}.
 *
 * A limit applies to every {@see ButtonSet} of a message, whatever its buttons' action. `null` means the channel
 * declares no limit, and nothing is checked against it. Core uses the limits for advice only: a flow that exceeds
 * them is warned about, never refused. A callback button's payload needs no limit here: it is at most
 * {@see Button::MAX_PAYLOAD_BYTES} bytes on every channel.
 *
 * Since foundation 0.32, experimental.
 */
final readonly class ButtonLimits
{
    /**
     * @param  int|null  $maxButtons  buttons in one message, all rows together
     * @param  int|null  $maxButtonsPerRow  buttons in one row
     * @param  int|null  $maxLabelLength  characters of one button label, counted as UTF-8 characters
     *
     * @throws InvalidArgumentException when a limit is zero or negative
     */
    public function __construct(
        public ?int $maxButtons = null,
        public ?int $maxButtonsPerRow = null,
        public ?int $maxLabelLength = null,
    ) {
        foreach (['maxButtons' => $maxButtons, 'maxButtonsPerRow' => $maxButtonsPerRow, 'maxLabelLength' => $maxLabelLength] as $name => $limit) {
            if (null !== $limit && $limit < 1) {
                throw new InvalidArgumentException("A button limit is at least 1 or null; {$name} is {$limit}.");
            }
        }
    }

    /**
     * Whether the channel declares no limit at all.
     */
    public function isUnlimited(): bool
    {
        return null === $this->maxButtons && null === $this->maxButtonsPerRow && null === $this->maxLabelLength;
    }
}
