<?php

declare(strict_types=1);

namespace Fapost\Foundation\Console\Bundle;

/**
 * One thing wrong in a `fapost-ui.json`: the field path and what is wrong with it.
 *
 * @experimental
 */
final readonly class UiBundleViolation
{
    /**
     * @param  string  $field  path in fapost-ui.json, such as "pages.Reviews/Index" or "css[1]"
     * @param  string  $message  what is wrong, such as "required"
     */
    public function __construct(
        public string $field,
        public string $message,
    ) {
    }

    /**
     * The line an operator reads: "<solution>: fapost-ui.json: <field>: <message>".
     *
     * @experimental
     */
    public function describe(string $solutionId): string
    {
        return sprintf('%s: fapost-ui.json: %s: %s', $solutionId, $this->field, $this->message);
    }
}
