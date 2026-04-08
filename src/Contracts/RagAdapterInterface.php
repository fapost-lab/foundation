<?php

declare(strict_types=1);

namespace FAPost\Foundation\Contracts;

use FAPost\Foundation\DTO\RagQueryContext;
use FAPost\Foundation\DTO\StructuredRagResult;

/**
 * Single contract between the flow engine and a RAG provider.
 *
 * Provider is an implementation detail. Replacing OpenAI with pgvector
 * should not require changes in the flow engine or nodes.
 *
 * Normalization from raw LLM output to StructuredRagResult happens
 * inside the adapter, not inside a node.
 */
interface RagAdapterInterface
{
    /**
     * Provider identifier. For example: "openai_assistants", "pgvector".
     * Used during registration in CoreRegistrar.
     */
    public function provider(): string;

    /**
     * Execute RAG query and return a deterministic result.
     *
     * Internally: LLM/vector DB call -> normalization -> StructuredRagResult.
     * Externally: always a predictable structure; flow engine is LLM-agnostic.
     */
    public function query(string $prompt, RagQueryContext $context): StructuredRagResult;
}
