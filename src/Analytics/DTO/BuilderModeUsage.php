<?php

declare(strict_types=1);

namespace Fapost\Foundation\Analytics\DTO;

use InvalidArgumentException;

/**
 * How many of a tenant's staff users have the flow builder set to each of its modes, as reported by
 * {@see \Fapost\Foundation\Analytics\Contracts\BuilderModeUsageInterface}.
 *
 * @experimental
 */
final readonly class BuilderModeUsage
{
    /** The tree layout: one branch level at a time. Also the mode of anyone who never chose one. */
    public const string TREE = 'tree';

    /** The graph layout: the whole flow on a canvas. */
    public const string GRAPH = 'graph';

    /** @var list<string> */
    public const array MODES = [self::TREE, self::GRAPH];

    /** Every counted staff user: `tree + graph`. */
    public int $total;

    /**
     * @param  int  $tree  staff users on the tree layout, at least 0
     * @param  int  $graph  staff users on the graph layout, at least 0
     *
     * @throws InvalidArgumentException when a count is negative
     */
    public function __construct(
        public int $tree,
        public int $graph,
    ) {
        if ($tree < 0 || $graph < 0) {
            throw new InvalidArgumentException('A builder mode count cannot be negative.');
        }

        $this->total = $tree + $graph;
    }
}
