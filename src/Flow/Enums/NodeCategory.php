<?php

declare(strict_types=1);

namespace Fapost\Foundation\Flow\Enums;

/**
 * Palette groups of the flow builder in Core.
 *
 * A node handler's `category()` returns one of these values or, for a Solution's node, the
 * Solution's id: the node then gets a palette group of its own, labelled with the manifest name.
 *
 * The order of the cases is the order of the palette. New cases may be added in a minor release.
 *
 * @experimental
 */
enum NodeCategory: string
{
    case Conversation = 'Conversation';
    case Logic        = 'Logic';
    case Data         = 'Data';
    case Contact      = 'Contact';
    case Integration  = 'Integration';
    case AI           = 'AI';
}
