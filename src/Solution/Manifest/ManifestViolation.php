<?php

declare(strict_types=1);

namespace Fapost\Foundation\Solution\Manifest;

/**
 * One thing wrong in a manifest: the field path and what is wrong with it.
 */
final readonly class ManifestViolation
{
    /**
     * @param  string  $field  path in the package's composer.json, such as "extra.fapost.id" or "extra.fapost.actions[1]"
     * @param  string  $message  what is wrong, such as "required"
     */
    public function __construct(
        public string $field,
        public string $message,
    ) {
    }

    /**
     * The line an operator reads: "<package>: <field>: <message>".
     */
    public function describe(string $package): string
    {
        return sprintf('%s: %s: %s', $package, $this->field, $this->message);
    }
}
