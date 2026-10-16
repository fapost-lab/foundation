<?php

declare(strict_types=1);

namespace Fapost\Foundation\Solution\Enums;

/**
 * Why an operator grants a tenant a Solution, which Core runs without the tenant turning it on.
 *
 * New sources may be added in a minor release: callers matching on it keep a default arm.
 */
enum SolutionGrantSource: string
{
    /** The tenant's plan bundles the Solution. */
    case Plan = 'plan';

    /** The tenant has a running add-on for the Solution: paid, on trial or given for free. */
    case Addon = 'addon';
}
