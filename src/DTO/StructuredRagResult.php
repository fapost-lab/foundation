<?php

declare(strict_types=1);

namespace FAPost\Foundation\DTO;

/**
 * Deterministic RAG query result.
 *
 * An LLM provider is non-deterministic. This object is the boundary between
 * the LLM world and the flow engine. The engine never sees raw LLM output.
 *
 * Stored in state["rag.*"] after the rag_query node execution.
 * The condition node reads rag.found and rag.confidence for deterministic branching.
 * The send_message node uses rag.answer as a regular outgoing message.
 */
final readonly class StructuredRagResult
{
    public function __construct(
        /** Whether a relevant answer was found in the knowledge base. */
        public bool $found,

        /** Provider confidence level for the answer. */
        public RagConfidence $confidence,

        /**
         * Final answer ready to send to the user.
         * Empty string when found = false.
         */
        public string $answer,

        /**
         * Detected request intent (optional).
         * Used for routing in condition nodes.
         */
        public ?string $intent = null,

        /**
         * Additional provider metadata.
         * Examples: sources, score, chunk_id.
         *
         * @var array<string, mixed>
         */
        public array $metadata = [],
    ) {
    }

    public static function notFound(): self
    {
        return new self(
            found: false,
            confidence: RagConfidence::Low,
            answer: '',
        );
    }

    /**
     * @return array<string, mixed> For writing into namespaced state
     */
    public function toStateArray(): array
    {
        return [
            'found'      => $this->found,
            'confidence' => $this->confidence->value,
            'answer'     => $this->answer,
            'intent'     => $this->intent,
            'metadata'   => $this->metadata,
        ];
    }
}
