<?php

declare(strict_types=1);

namespace Fapost\Foundation\Auth\Contracts;

use Fapost\Foundation\Auth\Enums\OneTimeCodeChannel;
use LogicException;

/**
 * The senders of one-time codes, one per channel. Implemented by Core.
 *
 * A package registers its sender from a service provider's `boot()`; the registry is closed once the application
 * has booted. A registered sender takes the channel over from Core's own (Core's email sender is only a default),
 * so an operator package can send the email codes its own way and an SMS connector makes SMS codes possible at
 * all. A channel nobody registered has no sender: a flow that asks for it is refused at publish.
 *
 * @experimental
 */
interface OneTimeCodeSenderRegistryInterface
{
    /**
     * @throws LogicException when another package already registered a sender for the channel, or the registry is closed
     */
    public function register(OneTimeCodeSenderInterface $sender): void;

    /**
     * The sender in effect for the channel: a registered one, otherwise Core's default; null when there is none.
     */
    public function find(OneTimeCodeChannel $channel): ?OneTimeCodeSenderInterface;
}
