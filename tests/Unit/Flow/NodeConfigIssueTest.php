<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Flow;

use Fapost\Foundation\Flow\DTO\NodeConfigIssue;
use Fapost\Foundation\Flow\Enums\NodeCategory;
use Fapost\Foundation\Flow\Enums\NodeConfigIssueSeverity;
use PHPUnit\Framework\TestCase;

final class NodeConfigIssueTest extends TestCase
{
    public function test_error_sets_the_error_severity(): void
    {
        $issue = NodeConfigIssue::error('missing_template', 'Pick a template.', 'config.template');

        $this->assertSame('missing_template', $issue->code);
        $this->assertSame('Pick a template.', $issue->message);
        $this->assertSame('config.template', $issue->field);
        $this->assertSame(NodeConfigIssueSeverity::Error, $issue->severity);
    }

    public function test_warning_sets_the_warning_severity_and_field_is_optional(): void
    {
        $issue = NodeConfigIssue::warning('unused_output', 'The output is never read.');

        $this->assertNull($issue->field);
        $this->assertSame(NodeConfigIssueSeverity::Warning, $issue->severity);
    }

    public function test_severity_values_are_stable(): void
    {
        $this->assertSame('error', NodeConfigIssueSeverity::Error->value);
        $this->assertSame('warning', NodeConfigIssueSeverity::Warning->value);
    }

    public function test_node_categories_keep_the_palette_order_and_values(): void
    {
        $this->assertSame(
            ['Conversation', 'Logic', 'Data', 'Contact', 'Integration', 'AI'],
            array_map(static fn (NodeCategory $category): string => $category->value, NodeCategory::cases()),
        );
    }
}
