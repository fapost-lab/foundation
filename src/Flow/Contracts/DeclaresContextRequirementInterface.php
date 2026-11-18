<?php

declare(strict_types=1);

namespace Fapost\Foundation\Flow\Contracts;

use Fapost\Foundation\Flow\Enums\NodeContextRequirement;

/**
 * Optional companion of a {@see \Fapost\Foundation\Contracts\NodeHandlerInterface}: a handler that also implements
 * this states what it needs from the run — a contact, none, or either.
 *
 * A handler that does not implement it needs a contact ({@see NodeContextRequirement::Contact}). Core checks the
 * declaration when it validates a flow for publishing: a flow that starts without a contact cannot hold a node that
 * needs one. The declaration covers the handler itself; what a node's config reads or writes (`contact.*`,
 * `module.*`, a contact variable) is checked by Core on top of it.
 *
 * A handler that declares {@see NodeContextRequirement::None} or {@see NodeContextRequirement::Any} must cope with
 * {@see \Fapost\Foundation\DTO\NodeExecutionContext::$contactId} and `$platform` being `null`, and with its state
 * reader refusing `contact.*` and `module.*`. It must return the same value on every call.
 */
interface DeclaresContextRequirementInterface
{
    public function contextRequirement(): NodeContextRequirement;
}
