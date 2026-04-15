<?php

declare(strict_types=1);

namespace FAPost\Foundation\Support;

use InvalidArgumentException;

/**
 * Value object for semantic versioning (semver 2.0.0).
 * Used in SolutionManifest and boot-time validation.
 */
final readonly class Version
{
    private function __construct(
        public int $major,
        public int $minor,
        public int $patch,
    ) {
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public static function parse(string $version): self
    {
        if ( ! self::isValid($version)) {
            throw new InvalidArgumentException("Invalid semver: '{$version}'");
        }

        [$major, $minor, $patch] = explode('.', $version);

        return new self((int)$major, (int)$minor, (int)$patch);
    }

    public static function isValid(string $version): bool
    {
        return (bool)preg_match('/^\d+\.\d+\.\d+$/', $version);
    }

    public function isGreaterThan(self $other): bool
    {
        return $this->toInt() > $other->toInt();
    }

    public function isGreaterThanOrEqual(self $other): bool
    {
        return $this->toInt() >= $other->toInt();
    }

    public function isLessThan(self $other): bool
    {
        return $this->toInt() < $other->toInt();
    }

    public function equals(self $other): bool
    {
        return $this->toInt() === $other->toInt();
    }

    public function toString(): string
    {
        return "{$this->major}.{$this->minor}.{$this->patch}";
    }

    private function toInt(): int
    {
        return $this->major * 1_000_000 + $this->minor * 1_000 + $this->patch;
    }
}
