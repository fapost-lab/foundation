<?php

declare(strict_types=1);

namespace FAPost\Foundation\DTO;

/**
 * Node execution result.
 *
 * A handler returns this object, and the engine decides what to do next:
 * move to another node, wait for input, complete the session, or fail it.
 */
final readonly class NodeExecutionResult
{
    public function __construct(
        public NodeExecutionStatus $status,

        /**
         * Next node ID for transition.
         * null when status = waiting or completed.
         */
        public ?string $nextNodeId = null,

        /**
         * Patch for namespaced state. It will be merged by the engine.
         * A handler writes only into its own namespace (flow.* or module.{name}.*).
         *
         * @var array<string, mixed>
         */
        public array $statePatch = [],

        /**
         * Error message when status = failed.
         * Logged in flow_logs and not shown directly to the user.
         */
        public ?string $errorMessage = null,
    )
    {
    }

    public static function transition(string $nextNodeId, array $statePatch = []): self
    {
        return new self(
            status: NodeExecutionStatus::Transition,
            nextNodeId: $nextNodeId,
            statePatch: $statePatch,
        );
    }

    public static function waiting(array $statePatch = []): self
    {
        return new self(
            status: NodeExecutionStatus::Waiting,
            statePatch: $statePatch,
        );
    }

    public static function completed(array $statePatch = []): self
    {
        return new self(
            status: NodeExecutionStatus::Completed,
            statePatch: $statePatch,
        );
    }

    public static function failed(string $errorMessage): self
    {
        return new self(
            status: NodeExecutionStatus::Failed,
            errorMessage: $errorMessage,
        );
    }
}
