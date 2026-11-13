<?php

declare(strict_types=1);

namespace Fapost\Foundation\Flow\Enums;

/**
 * How serious a {@see \Fapost\Foundation\Flow\DTO\NodeConfigIssue} is: an error blocks publishing
 * the flow, a warning is shown to the author and does not.
 *
 * @experimental
 */
enum NodeConfigIssueSeverity: string
{
    case Error   = 'error';
    case Warning = 'warning';
}
