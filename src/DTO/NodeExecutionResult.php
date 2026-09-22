<?php

declare(strict_types=1);

namespace Fapost\Foundation\DTO;

use DateTimeInterface;

/**
 * Node execution result returned by {@see \Fapost\Foundation\Contracts\NodeHandlerInterface::execute()}.
 *
 * On {@see NodeExecutionStatus::Executed}, the engine resolves the next node using flow edges:
 * {@code source_node_id} + {@code transition} (source handle), unless there is no matching edge
 * (flow ends).
 *
 * Mutations outside the session JSON (Contact attributes, Contact.language, etc.) are performed
 * directly by the handler via {@see \Fapost\Foundation\Flow\Contracts\ContactWriterInterface}
 * passed through {@see NodeExecutionContext}. The legacy {@code effects[]} array was removed
 * in Phase E; handlers must use the writer.
 */
final readonly class NodeExecutionResult
{
    /**
     * @param  array<string, mixed>  $stateChanges  Namespaced flat keys, e.g. {@code flow.answer} => value
     * @param  array<string, mixed>  $logResolved   Snapshot values persisted to flow_logs.resolved
     * @param  array<string, mixed>  $metadata
     * @param  DateTimeInterface|null  $resumeAt  For {@see NodeExecutionStatus::Delayed}: when the engine runs the node again
     */
    public function __construct(
        public NodeExecutionStatus $status,
        public ?string $sourceHandle = null,
        public array $stateChanges = [],
        public array $logResolved = [],
        public array $metadata = [],
        public ?string $errorMessage = null,
        public ?DateTimeInterface $resumeAt = null,
    ) {
    }

    /**
     * @param  array<string, mixed>  $stateChanges
     * @param  array<string, mixed>  $logResolved
     * @param  array<string, mixed>  $metadata
     */
    public static function executed(
        ?string $sourceHandle = 'default',
        array $stateChanges = [],
        array $logResolved = [],
        array $metadata = [],
    ): self {
        return new self(
            status: NodeExecutionStatus::Executed,
            sourceHandle: $sourceHandle,
            stateChanges: $stateChanges,
            logResolved: $logResolved,
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
     * Pause the node and continue it later.
     *
     * With {@code $resumeAt} the session waits on `paused`: messages from the
     * contact meanwhile are answered as busy, and at {@code $resumeAt} (or with the
     * first message after it) the engine runs this node again with
     * {@see NodeExecutionContext::$resumedAfterDelay} set. Without it the node
     * waits like {@see waiting()}: the contact's next message runs it again.
     *
     * @param  array<string, mixed>  $stateChanges
     * @param  array<string, mixed>  $metadata
     */
    public static function delayed(
        array $stateChanges = [],
        array $metadata = [],
        ?DateTimeInterface $resumeAt = null,
    ): self {
        return new self(
            status: NodeExecutionStatus::Delayed,
            stateChanges: $stateChanges,
            metadata: $metadata,
            resumeAt: $resumeAt,
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
            metadata: $metadata,
            errorMessage: $errorMessage,
        );
    }
}
