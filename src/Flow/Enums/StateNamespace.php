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
 * This enum is part of the Foundation extension contract: Solutions and
 * Plugins consume it to declare allowed namespaces in schema fields and to
 * resolve paths through DataAccessor/VariableResolver. New top-level
 * namespaces are added here, not invented per node.
 */
enum StateNamespace: string
{
    case System  = 'system';
    case Flow    = 'flow';
    case Rag     = 'rag';
    case Module  = 'module';
    case Contact = 'contact';
    case Call    = 'call';
}
