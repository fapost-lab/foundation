<?php

declare(strict_types=1);

namespace Fapost\Foundation\Auth\DTO;

use InvalidArgumentException;

/**
 * What a {@see \Fapost\Foundation\Auth\Contracts\OneTimeCodeSenderInterface} reports about one delivery.
 *
 * A failure carries a short reason for the operator's log, never the code or the destination.
 *
 * @experimental
 */
final readonly class OneTimeCodeDelivery
{
    /**
     * @throws InvalidArgumentException when a failure has no reason
     */
    private function __construct(
        public bool $sent,
        public ?string $providerMessageId,
        public ?string $error,
    ) {
        if (!$sent && (null === $error || '' === mb_trim($error))) {
            throw new InvalidArgumentException('A failed one-time code delivery needs a reason.');
        }
    }

    /**
     * The provider accepted the message.
     */
    public static function sent(?string $providerMessageId = null): self
    {
        return new self(true, $providerMessageId, null);
    }

    /**
     * The provider refused the message or could not be reached.
     *
     * @param  string  $error  a short reason without the code or the destination
     */
    public static function failed(string $error): self
    {
        return new self(false, null, $error);
    }
}
