<?php

declare(strict_types=1);

namespace Fapost\Foundation\Flow\Enums;

/**
 * What a node needs from the run it executes in, declared by its handler through
 * {@see \Fapost\Foundation\Flow\Contracts\DeclaresContextRequirementInterface}. A handler that declares nothing needs a
 * contact, so a node nobody checked is never placed in a flow that starts without one.
 *
 * - `Contact` — the node reaches the contact of the run: sends to it, waits for its answer, reads or writes it.
 * - `None`    — the node never uses a contact.
 * - `Any`     — the node runs with a contact or without one, and uses the contact only when there is one.
 *
 * A flow whose trigger starts it without a contact (a schedule, a webhook, an API call) cannot be published while it
 * holds a `Contact` node; `None` and `Any` are refused nowhere.
 */
enum NodeContextRequirement: string
{
    case Contact = 'contact';
    case None    = 'none';
    case Any     = 'any';
}
