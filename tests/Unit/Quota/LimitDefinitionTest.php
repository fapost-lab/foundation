<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Quota;

use Fapost\Foundation\Quota\DTO\LimitDefinition;
use Fapost\Foundation\Quota\Enums\LimitKind;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LimitDefinitionTest extends TestCase
{
    public function test_a_definition_carries_its_values(): void
    {
        $limit = new LimitDefinition('monthly_active_contacts', 'Monthly active contacts', 'contacts', LimitKind::PerPeriod, 'Distinct contacts per period.');

        $this->assertSame('monthly_active_contacts', $limit->key);
        $this->assertSame('Monthly active contacts', $limit->label);
        $this->assertSame('contacts', $limit->unit);
        $this->assertSame(LimitKind::PerPeriod, $limit->kind);
        $this->assertSame('Distinct contacts per period.', $limit->description);
    }

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function invalid(): array
    {
        return [
            'upper case key'  => ['Assistants', 'Assistants', 'assistants'],
            'dashed key'      => ['active-contacts', 'Contacts', 'contacts'],
            'leading digit'   => ['1st', 'First', 'items'],
            'trailing _'      => ['assistants_', 'Assistants', 'assistants'],
            'empty label'     => ['assistants', ' ', 'assistants'],
            'empty unit'      => ['assistants', 'Assistants', ''],
        ];
    }

    #[DataProvider('invalid')]
    public function test_an_invalid_definition_is_refused(string $key, string $label, string $unit): void
    {
        $this->expectException(InvalidArgumentException::class);

        new LimitDefinition($key, $label, $unit, LimitKind::Records);
    }

    public function test_the_kind_values_are_stable(): void
    {
        $this->assertSame(['records', 'per_period', 'bytes'], array_map(static fn (LimitKind $k): string => $k->value, LimitKind::cases()));
    }
}
