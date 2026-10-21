<?php

declare(strict_types=1);

namespace Fapost\Foundation\Messaging;

use InvalidArgumentException;

/**
 * The buttons of one message as rows, channel-neutral. See {@see MessagePayload::$buttons}.
 *
 * A set either has callback buttons only or none at all: a channel draws callback buttons and the other kinds
 * in different places, so they never share one message.
 *
 * Since foundation 0.26, experimental.
 */
final readonly class ButtonSet
{
    /** @var list<list<Button>> */
    public array $rows;

    /**
     * Rows and the buttons in each row are re-indexed, so a set built from keyed arrays still serialises as lists.
     *
     * @param  array<array-key, array<array-key, Button>>  $rows
     *
     * @throws InvalidArgumentException when there are no rows, a row is empty, an element is not a {@see Button},
     *                                  or callback buttons are mixed with other actions
     */
    public function __construct(array $rows)
    {
        if ([] === $rows) {
            throw new InvalidArgumentException('A button set needs at least one row.');
        }

        $callbacks  = 0;
        $total      = 0;
        $normalized = [];

        foreach ($rows as $row) {
            if (! is_array($row) || [] === $row) {
                throw new InvalidArgumentException('A button set row needs at least one button.');
            }

            $row = array_values($row);

            foreach ($row as $button) {
                if (! $button instanceof Button) {
                    throw new InvalidArgumentException('A button set holds only buttons.');
                }

                ++$total;

                if (ButtonAction::Callback === $button->action) {
                    ++$callbacks;
                }
            }

            $normalized[] = $row;
        }

        $this->rows = $normalized;

        if (0 !== $callbacks && $callbacks !== $total) {
            throw new InvalidArgumentException('Callback buttons cannot be mixed with other actions in one set.');
        }
    }

    /**
     * Whether the buttons are callback buttons, as opposed to reply, contact or location buttons.
     */
    public function expectsCallback(): bool
    {
        return ButtonAction::Callback === $this->rows[0][0]->action;
    }
}
