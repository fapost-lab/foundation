<?php

declare(strict_types=1);

namespace Fapost\Foundation\Console\Contracts;

use Fapost\Foundation\Console\DTO\ColumnContext;

/**
 * Supplies the values of a Solution's table column. Resolved from the container; one class may serve
 * several columns, told apart by `$context->key`. Core catches what it throws and leaves the column empty.
 *
 * @experimental
 */
interface ColumnValuesInterface
{
    /**
     * Called once per table page, inside the tenant context.
     *
     * @param  list<string>  $ids  row ids of the page
     *
     * @return array<string, string|int|float|bool|null>  keyed by row id; a missing id renders empty
     *
     * @experimental
     */
    public function values(array $ids, ColumnContext $context): array;
}
