<?php

declare(strict_types=1);

namespace FAPost\Foundation\DTO;

/**
 * Node execution result returned by {@see \FAPost\Foundation\Contracts\NodeHandlerInterface::execute()}.
 *
 * On {@see NodeExecutionStatus::Completed}, the engine resolves the next node using flow edges:
 * {@code source_node_id} + {@code transition} (source handle), unless there is no matching edge
 * (flow ends).
 */
final readonly class NodeExecutionResult
{
    /**
     * @param  array<string, mixed>  $stateChanges  Namespaced flat keys, e.g. {@code flow.answer} => value
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public NodeExecutionStatus $status,
        public ?string $sourceHandle = null,
        public array $stateChanges = [],
        public array $metadata = [],
        public ?string $errorMessage = null,
    ) {
    }

    /**
     * @param  array<string, mixed>  $stateChanges
     * @param  array<string, mixed>  $metadata
     */
    public static function completed(
        ?string $sourceHandle = 'default',
        array $stateChanges = [],
        array $metadata = [],
    ): self {
        return new self(
            status: NodeExecutionStatus::Completed,
            sourceHandle: $sourceHandle,
            stateChanges: $stateChanges,
            metadata: $metadata,
        );
    }

    /**
     * @param  array<string, mixed>  $stateChanges
     * @param  array<string, mixed>  $metadata
     */
    public static function waiting(array $stateChanges = [], array $metadata = []): self
    {
        return new self(
            status: NodeExecutionStatus::Waiting,
            stateChanges: $stateChanges,
            metadata: $metadata,
        );
    }

    /**
     * @param  array<string, mixed>  $stateChanges
     * @param  array<string, mixed>  $metadata
     */
    public static function delayed(array $stateChanges = [], array $metadata = []): self
    {
        return new self(
            status: NodeExecutionStatus::Delayed,
            stateChanges: $stateChanges,
            metadata: $metadata,
        );
    }

    /**
     * @param  array<string, mixed>  $stateChanges
     * @param  array<string, mixed>  $metadata
     */
    public static function finished(array $stateChanges = [], array $metadata = []): self
    {
        return new self(
            status: NodeExecutionStatus::Finished,
            stateChanges: $stateChanges,
            metadata: $metadata,
        );
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public static function failed(string $errorMessage, array $metadata = []): self
    {
        return new self(
            status: NodeExecutionStatus::Failed,
            errorMessage: $errorMessage,
            metadata: $metadata,
        );
    }
}
