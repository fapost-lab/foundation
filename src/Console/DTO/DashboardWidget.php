<?php

declare(strict_types=1);

namespace Fapost\Foundation\Console\DTO;

use Fapost\Foundation\Console\Enums\DashboardTarget;
use Fapost\Foundation\Console\Enums\WidgetSize;
use Fapost\Foundation\Console\Internal\DeclarationFormat;
use InvalidArgumentException;

/**
 * A component a Solution places on a dashboard.
 *
 * @experimental
 */
final readonly class DashboardWidget
{
    /**
     * @param  string  $key  unique per Solution and dashboard
     * @param  string  $component  "<id>::<Path>", listed under `components` in the Solution's `fapost-ui.json`
     * @param  string|null  $ability  Gate ability to see the widget
     * @param  int  $order  lower first; Core's own widgets come before every Solution's
     *
     * @throws InvalidArgumentException when a name does not have its format
     */
    public function __construct(
        public DashboardTarget $target,
        public string $key,
        public string $component,
        public WidgetSize $size = WidgetSize::Single,
        public ?string $ability = null,
        public int $order = 0,
    ) {
        $what = sprintf('Dashboard widget "%s"', $key);

        DeclarationFormat::key('Dashboard widget', $key);
        DeclarationFormat::component($what, $component);
        DeclarationFormat::ability($what, $ability);
    }
}
