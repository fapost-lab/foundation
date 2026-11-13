<?php

declare(strict_types=1);

namespace Fapost\Foundation\Flow\Enums;

/**
 * Canonical top-level namespaces of {@code flow_sessions.state}.
 *
 * The flow engine partitions session state by owner: {@see System} is written
 * by the engine and whitelisted system handlers, {@see Flow} by input/assign
 * nodes, {@see Rag} by rag_query, {@see Module} via DataAccessorInterface
 * (read-only, lazy). {@see Contact} and {@see Call} are derived projections —
 * the contact profile and the last HTTP call response respectively — that
 * authors can address with the same dotted-path notation in templates,
 * branch conditions, and state pickers.
 *
 * The enum lists the built-in namespaces. A Solution with `state: true` in its manifest owns one
 * more namespace, named exactly by its id, writable only by that Solution's own node types. The full
 * list at runtime is {@see \Fapost\Foundation\Flow\Contracts\StateNamespacesInterface}; this enum is
 * not it. New built-in namespaces are added here, not invented per node.
 */
enum StateNamespace: string
{
    case System = 'system';
    case Flow   = 'flow';
    /**
     * @deprecated removed in the next major release; the RAG capability moves to a Solution
     */
    case Rag     = 'rag';
    case Module  = 'module';
    case Contact = 'contact';
    case Call    = 'call';
}
