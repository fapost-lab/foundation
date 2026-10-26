<?php

declare(strict_types=1);

namespace Fapost\Foundation\Messaging;

use InvalidArgumentException;

/**
 * The {@see ChannelCapability} set a channel declares. A channel that declares nothing handles text and media only.
 *
 * Beside the flags, {@see ButtonLimits} say how many buttons the channel shows and how long their labels may be
 * (since foundation 0.32); a channel that declares none has no known limit.
 *
 * Since foundation 0.26, experimental.
 */
final readonly class ChannelCapabilities
{
    /**
     * @var list<ChannelCapability>
     */
    public array $features;

    /**
     * @param  list<ChannelCapability>  $features  duplicates are dropped
     * @param  ButtonLimits  $buttonLimits  how many buttons the channel shows and how long their labels may be
     *
     * @throws InvalidArgumentException when an element is not a {@see ChannelCapability}
     */
    public function __construct(array $features = [], public ButtonLimits $buttonLimits = new ButtonLimits())
    {
        $unique = [];

        foreach ($features as $feature) {
            if (! $feature instanceof ChannelCapability) {
                throw new InvalidArgumentException('Channel capabilities hold only ChannelCapability cases.');
            }

            $unique[$feature->value] = $feature;
        }

        $this->features = array_values($unique);
    }

    public function supports(ChannelCapability $capability): bool
    {
        return in_array($capability, $this->features, true);
    }
}
