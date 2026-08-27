<?php

declare(strict_types=1);

namespace Fapost\Foundation\Flow\Contracts;

use Fapost\Foundation\Flow\DTO\ExpressionContext;

/**
 * Pluggable expression evaluation strategy for flow nodes.
 *
 * Used wherever a flow definition contains user-authored expressions:
 * SendMessage.text, Call.target/parameters, Assign.value, RagQuery.query,
 * EmitEvent.payload, Input prompts, etc. Branch conditions are NOT covered
 * here — they use a separate structured-JSON contract that is engine-agnostic.
 *
 * Selection model (see ADR Expression Language):
 *  - Engine is chosen per-tenant via tenant.settings.expression_engine
 *  - flow_definitions.expression_engine is an immutable snapshot — running
 *    sessions always evaluate against the engine that was active when the
 *    definition was published, regardless of subsequent tenant config drift
 *  - V1 ships built-in engine "template" (regex {@code {{path}}} substitution)
 *  - Plugins/Solutions register additional engines via CoreRegistrar
 *
 * Implementations register themselves in the in-memory ExpressionEngineRegistry
 * during boot. Conflicts on id() cause boot failure.
 */
interface ExpressionEngineInterface
{
    /**
     * Stable identifier of this engine in the registry.
     *
     * Examples: "template", "symfony_el", "twig", "myplugin_dsl_v2".
     */
    public function id(): string;

    /**
     * Engine version. Bump on syntax-breaking changes; recommended approach for
     * incompatible variants is to register a new id() rather than version
     * within the same id, so existing flow_definitions keep working.
     */
    public function version(): int;

    /**
     * Evaluate a source expression against the runtime context.
     *
     * For substitution-style engines (template) the result is a string with
     * placeholders replaced. For language-style engines (Symfony EL) the result
     * may be any scalar/array depending on the expression itself.
     *
     * Implementations document their own behaviour for unknown paths —
     * {@code TemplateEngine} substitutes empty string, others may throw.
     *
     * @throws ExpressionEvaluationException
     */
    public function evaluate(string $source, ExpressionContext $context): mixed;

    /**
     * Validate the syntax of a source expression at flow_definition save time.
     * Does not execute side effects; only checks parsability and any static
     * reference rules supported by the engine.
     *
     * @throws ExpressionSyntaxException
     */
    public function validate(string $source): void;

    /**
     * Extract every state path referenced by the expression.
     *
     * Used by group-discovery cache invalidation, builder UI hints, and
     * static analysis tooling. Engines that cannot statically analyse their
     * own syntax may return an empty array.
     *
     * Example for "template": "Hi, {{contact.first_name}} from {{contact.city}}"
     *  → ["contact.first_name", "contact.city"]
     *
     * @return list<string>
     */
    public function extractReferences(string $source): array;
}
