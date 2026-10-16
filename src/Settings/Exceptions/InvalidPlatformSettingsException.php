<?php

declare(strict_types=1);

namespace Fapost\Foundation\Settings\Exceptions;

use InvalidArgumentException;

/**
 * Thrown by {@see \Fapost\Foundation\Settings\Contracts\PlatformSettingsInterface::update()} when
 * any change is refused; nothing was written. {@see $violations} names each refused key.
 *
 * @experimental
 */
final class InvalidPlatformSettingsException extends InvalidArgumentException
{
    /**
     * @param  array<string, string>  $violations  key => why it was refused, in English
     */
    private function __construct(public readonly array $violations)
    {
        parent::__construct(implode(' ', array_map(
            static fn (string $key, string $why): string => sprintf('%s: %s', $key, $why),
            array_keys($violations),
            $violations,
        )));
    }

    /**
     * @param  array<string, string>  $violations  key => why it was refused, in English; not empty
     *
     * @throws InvalidArgumentException when there is no violation to report
     */
    public static function because(array $violations): self
    {
        if ([] === $violations) {
            throw new InvalidArgumentException('An invalid settings exception needs at least one violation.');
        }

        return new self($violations);
    }
}
