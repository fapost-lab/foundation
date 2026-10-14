<?php

declare(strict_types=1);

namespace Fapost\Foundation\Console\Bundle;

/**
 * The built console UI of a Solution, as declared by its `fapost-ui.json`. Read with {@see UiBundleParser}.
 *
 * @experimental
 */
final readonly class UiBundle
{
    /**
     * @param  string  $solution  the Solution id
     * @param  string  $kit  version constraint of the console kit the bundle was built against
     * @param  string  $vue  version constraint of Vue
     * @param  string  $inertia  version constraint of Inertia
     * @param  array<string, string>  $pages  Path => file relative to the bundle's directory
     * @param  array<string, string>  $components  Path => file relative to the bundle's directory
     * @param  list<string>  $css  files relative to the bundle's directory
     */
    public function __construct(
        public int $schema,
        public string $solution,
        public string $kit,
        public string $vue,
        public string $inertia,
        public array $pages,
        public array $components,
        public array $css,
    ) {
    }
}
