<?php

declare(strict_types=1);

namespace Fapost\Foundation\Console\DTO;

use Fapost\Foundation\Console\Enums\TableTarget;
use Fapost\Foundation\Console\Internal\DeclarationFormat;
use InvalidArgumentException;

/**
 * Which column a {@see \Fapost\Foundation\Console\Contracts\ColumnValuesInterface} is asked to fill.
 *
 * @experimental
 */
final readonly class ColumnContext
{
    /**
     * @param  string  $key  the key of the TableColumn, so one class can serve several columns
     * @param  string|null  $assistantId  the assistant the table is shown on, or null on a tenant-wide table
     *
     * @throws InvalidArgumentException when the key does not have its format or the assistant id is empty
     */
    public function __construct(
        public TableTarget $target,
        public string $key,
        public ?string $assistantId,
    ) {
        DeclarationFormat::key('Table column', $key);

        if ('' === $assistantId) {
            throw new InvalidArgumentException('Column context assistant id must be null or non-empty.');
        }
    }
}
