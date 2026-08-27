<?php

declare(strict_types=1);

namespace Fapost\Foundation\Flow\DTO;

use Fapost\Foundation\Flow\Contracts\ScopedStateReaderInterface;

/**
 * Runtime context passed to {@see \Fapost\Foundation\Flow\Contracts\ExpressionEngineInterface::evaluate()}.
 *
 * Carries the identifiers and the reader needed for an engine to resolve
 * placeholders or evaluate expressions against current session/contact/module
 * state. Domain objects (Contact, Session, Tenant) are intentionally absent —
 * engine implementations resolve everything through the reader to keep
 * Foundation free of Core domain abstractions.
 */
final readonly class ExpressionContext
{
    public function __construct(
        public string $tenantId,
        public string $contactId,
        public string $sessionId,
        public ScopedStateReaderInterface $stateReader,
    )
    {
    }

    /**
     * Convenience accessor — read a path from the underlying state reader.
     */
    public function read(string $path): mixed
    {
        return $this->stateReader->read($path);
    }
}
