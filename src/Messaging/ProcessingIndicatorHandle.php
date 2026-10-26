<?php

declare(strict_types=1);

namespace Fapost\Foundation\Messaging;

/**
 * Opaque ticket returned by {@see TypingCapableProviderInterface::indicateProcessing()}.
 *
 * The shape of {@code $providerData} is provider-specific (a presence subscription id,
 * for example). It never holds credentials: the provider gets the channel on every call. Callers must
 * not depend on its contents — pass the handle back to the same provider's
 * {@code stopProcessing()} or {@code refreshProcessing()} method as-is.
 */
final readonly class ProcessingIndicatorHandle
{
    public function __construct(
        public string $providerId,
        public string $chatId,
        /** @var array<string, mixed> */
        public array $providerData = [],
    ) {
    }
}
