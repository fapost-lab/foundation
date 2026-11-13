<?php

declare(strict_types=1);

namespace Fapost\Foundation\Flow\DTO;

use Fapost\Foundation\Flow\Enums\NodeConfigIssueSeverity;

/**
 * One problem a node handler found in a node's configuration.
 *
 * @experimental
 */
final readonly class NodeConfigIssue
{
    /**
     * @param  string  $code  stable machine-readable code, such as "missing_template"
     * @param  string  $message  what the flow author reads
     * @param  string|null  $field  the config field the issue is about, or null when it concerns the node as a whole
     */
    public function __construct(
        public string $code,
        public string $message,
        public ?string $field = null,
        public NodeConfigIssueSeverity $severity = NodeConfigIssueSeverity::Error,
    ) {
    }

    public static function error(string $code, string $message, ?string $field = null): self
    {
        return new self($code, $message, $field, NodeConfigIssueSeverity::Error);
    }

    public static function warning(string $code, string $message, ?string $field = null): self
    {
        return new self($code, $message, $field, NodeConfigIssueSeverity::Warning);
    }
}
