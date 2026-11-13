<?php

declare(strict_types=1);

namespace Fapost\Foundation\Flow\Contracts;

use Fapost\Foundation\Flow\DTO\NodeConfigIssue;

/**
 * Optional companion of a {@see \Fapost\Foundation\Contracts\NodeHandlerInterface}: a handler that
 * also implements this validates its own nodes.
 *
 * Core calls it when it validates a flow for publishing, after its generic checks against
 * `configSchema()`. It must be pure: no I/O, no state, the same node always gives the same issues.
 *
 * @experimental
 */
interface NodeConfigValidatorInterface
{
    /**
     * @param  array<string, mixed>  $node  node payload from the flow definition JSON (id, type, version, config, …)
     * @return list<NodeConfigIssue>
     */
    public function validate(array $node): array;
}
