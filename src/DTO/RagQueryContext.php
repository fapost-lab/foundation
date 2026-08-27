<?php

declare(strict_types=1);

namespace Fapost\Foundation\DTO;

/**
 * RAG query context passed to RagAdapterInterface::query().
 */
final readonly class RagQueryContext
{
    public function __construct(
        /** Knowledge base ID (knowledge_bases.id) */
        public string $knowledgeBaseId,

        /** Tenant ID */
        public string $tenantId,

        /** Language context (optional, useful for multilingual knowledge bases) */
        public ?string $language = null,

        /**
         * Additional provider-specific options.
         * For example: top_k, temperature, assistant_id for OpenAI.
         *
         * @var array<string, mixed>
         */
        public array $providerOptions = [],
    ) {
    }
}
