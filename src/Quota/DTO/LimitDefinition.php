<?php

declare(strict_types=1);

namespace Fapost\Foundation\Quota\DTO;

use Fapost\Foundation\Quota\Enums\LimitKind;
use InvalidArgumentException;

/**
 * One limit a platform can enforce, as registered in a
 * {@see \Fapost\Foundation\Quota\Contracts\LimitRegistryInterface}.
 */
final readonly class LimitDefinition
{
    /**
     * @param  string  $key  stable identifier in snake_case, such as "assistants"; plans store limits under it
     * @param  string  $label  what an operator reads, in English, such as "Assistants"
     * @param  string  $unit  what one unit is, in English, such as "assistants" or "messages"
     *
     * @throws InvalidArgumentException when the key is not snake_case or the label or unit is empty
     */
    public function __construct(
        public string $key,
        public string $label,
        public string $unit,
        public LimitKind $kind,
        public ?string $description = null,
    ) {
        if (1 !== preg_match('/^[a-z][a-z0-9]*(_[a-z0-9]+)*$/', $key)) {
            throw new InvalidArgumentException(sprintf('Limit key "%s" must be snake_case.', $key));
        }

        if ('' === mb_trim($label) || '' === mb_trim($unit)) {
            throw new InvalidArgumentException(sprintf('Limit "%s" needs a label and a unit.', $key));
        }
    }
}
