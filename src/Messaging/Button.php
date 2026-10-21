<?php

declare(strict_types=1);

namespace Fapost\Foundation\Messaging;

use InvalidArgumentException;

/**
 * One button of a {@see ButtonSet}, channel-neutral.
 *
 * A {@see ButtonAction::Callback} button carries an opaque `payload` of at most {@see self::MAX_PAYLOAD_BYTES}
 * bytes, the strictest limit of the channels known so far; a connector hands it back untouched when the button is
 * pressed. No other action carries a payload.
 *
 * Since foundation 0.26, experimental.
 */
final readonly class Button
{
    public const int MAX_PAYLOAD_BYTES = 64;

    /**
     * @param  string  $label  text shown on the button
     * @param  ButtonAction  $action  what the press does
     * @param  string|null  $payload  opaque value returned on a press; required for, and only for, a callback button
     *
     * @throws InvalidArgumentException when the label is empty, a callback button has no payload or one longer
     *                                  than {@see self::MAX_PAYLOAD_BYTES} bytes, or another action carries a payload
     */
    public function __construct(
        public string $label,
        public ButtonAction $action = ButtonAction::Callback,
        public ?string $payload = null,
    ) {
        if ('' === $label) {
            throw new InvalidArgumentException('A button needs a non-empty label.');
        }

        if (ButtonAction::Callback !== $action) {
            if (null !== $payload) {
                throw new InvalidArgumentException("A {$action->value} button carries no payload.");
            }

            return;
        }

        if (null === $payload || '' === $payload) {
            throw new InvalidArgumentException('A callback button needs a non-empty payload.');
        }

        if (mb_strlen($payload, '8bit') > self::MAX_PAYLOAD_BYTES) {
            throw new InvalidArgumentException(
                'A callback button payload is at most ' . self::MAX_PAYLOAD_BYTES . ' bytes.'
            );
        }
    }
}
