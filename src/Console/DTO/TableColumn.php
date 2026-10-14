<?php

declare(strict_types=1);

namespace Fapost\Foundation\Console\DTO;

use Fapost\Foundation\Console\Contracts\ColumnValuesInterface;
use Fapost\Foundation\Console\Enums\CellType;
use Fapost\Foundation\Console\Enums\TableTarget;
use Fapost\Foundation\Console\Enums\Tone;
use Fapost\Foundation\Console\Internal\DeclarationFormat;
use InvalidArgumentException;

/**
 * A column a Solution adds to a Core table. Core renders the cells; the Solution supplies only the values.
 *
 * @experimental
 */
final readonly class TableColumn
{
    /**
     * @param  string  $key  unique per Solution and table
     * @param  string  $label  translation key in the Solution's namespace
     * @param  class-string<ColumnValuesInterface>  $values  resolved from the container once per table page
     * @param  string|null  $ability  Gate ability to see the column
     * @param  int  $order  lower first; Solution columns come after Core's own
     * @param  array<string, Tone>  $tones  for CellType::Status: value => tone; other cell types ignore it
     *
     * @throws InvalidArgumentException when a name does not have its format or a tone is not a Tone
     */
    public function __construct(
        public TableTarget $target,
        public string $key,
        public string $label,
        public CellType $cell,
        public string $values,
        public ?string $ability = null,
        public int $order = 0,
        public array $tones = [],
    ) {
        $what = sprintf('Table column "%s"', $key);

        DeclarationFormat::key('Table column', $key);
        DeclarationFormat::label($what, $label);
        DeclarationFormat::className($what . ' values', $values);
        DeclarationFormat::ability($what, $ability);

        foreach ($tones as $value => $tone) {
            if ('' === (string) $value || !$tone instanceof Tone) {
                throw new InvalidArgumentException(sprintf('%s tones must map non-empty values to %s.', $what, Tone::class));
            }
        }
    }
}
