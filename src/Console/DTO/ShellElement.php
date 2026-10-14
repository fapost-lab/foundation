<?php

declare(strict_types=1);

namespace Fapost\Foundation\Console\DTO;

use Fapost\Foundation\Console\Enums\ShellSlot;
use Fapost\Foundation\Console\Internal\DeclarationFormat;
use InvalidArgumentException;

/**
 * A component a Solution mounts in the console shell, on every console page.
 *
 * @experimental
 */
final readonly class ShellElement
{
    /**
     * @param  string  $key  unique per Solution and slot
     * @param  string  $component  "<id>::<Path>", listed under `components` in the Solution's `fapost-ui.json`
     * @param  string|null  $ability  Gate ability to see the element
     * @param  bool  $perAssistant  shown only on an assistant's pages
     * @param  int  $order  lower first
     *
     * @throws InvalidArgumentException when a name does not have its format
     */
    public function __construct(
        public ShellSlot $slot,
        public string $key,
        public string $component,
        public ?string $ability = null,
        public bool $perAssistant = false,
        public int $order = 0,
    ) {
        $what = sprintf('Shell element "%s"', $key);

        DeclarationFormat::key('Shell element', $key);
        DeclarationFormat::component($what, $component);
        DeclarationFormat::ability($what, $ability);
    }
}
