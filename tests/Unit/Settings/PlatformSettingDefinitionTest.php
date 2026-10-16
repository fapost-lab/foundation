<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Settings;

use Fapost\Foundation\Settings\DTO\PlatformSettingDefinition;
use Fapost\Foundation\Settings\Enums\PlatformSettingType;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PlatformSettingDefinitionTest extends TestCase
{
    /**
     * @return array<string, array{callable(): PlatformSettingDefinition}>
     */
    public static function invalid(): array
    {
        return [
            'undotted key'           => [static fn (): PlatformSettingDefinition => new PlatformSettingDefinition('max_iterations', PlatformSettingType::Integer, 1, 'L', 'D', 'G')],
            'upper case key'         => [static fn (): PlatformSettingDefinition => new PlatformSettingDefinition('Flow.max', PlatformSettingType::Integer, 1, 'L', 'D', 'G')],
            'trailing dot'           => [static fn (): PlatformSettingDefinition => new PlatformSettingDefinition('flow.', PlatformSettingType::Integer, 1, 'L', 'D', 'G')],
            'empty label'            => [static fn (): PlatformSettingDefinition => new PlatformSettingDefinition('flow.max', PlatformSettingType::Integer, 1, ' ', 'D', 'G')],
            'empty description'      => [static fn (): PlatformSettingDefinition => new PlatformSettingDefinition('flow.max', PlatformSettingType::Integer, 1, 'L', '', 'G')],
            'empty group'            => [static fn (): PlatformSettingDefinition => new PlatformSettingDefinition('flow.max', PlatformSettingType::Integer, 1, 'L', 'D', '')],
            'min above max'          => [static fn (): PlatformSettingDefinition => new PlatformSettingDefinition('flow.max', PlatformSettingType::Integer, 5, 'L', 'D', 'G', min: 10, max: 1)],
            'choice without choices' => [static fn (): PlatformSettingDefinition => new PlatformSettingDefinition('ui.mode', PlatformSettingType::Choice, 'a', 'L', 'D', 'G')],
            'choices on an integer'  => [static fn (): PlatformSettingDefinition => new PlatformSettingDefinition('flow.max', PlatformSettingType::Integer, 1, 'L', 'D', 'G', choices: ['a'])],
            'default out of range'   => [static fn (): PlatformSettingDefinition => new PlatformSettingDefinition('flow.max', PlatformSettingType::Integer, 0, 'L', 'D', 'G', min: 1)],
            'default of wrong type'  => [static fn (): PlatformSettingDefinition => new PlatformSettingDefinition('flow.on', PlatformSettingType::Boolean, 1, 'L', 'D', 'G')],
            'default not a choice'   => [static fn (): PlatformSettingDefinition => new PlatformSettingDefinition('ui.mode', PlatformSettingType::Choice, 'c', 'L', 'D', 'G', choices: ['a', 'b'])],
        ];
    }
    public function test_a_definition_carries_its_values(): void
    {
        $definition = $this->integer(min: 10, max: 10000, default: 100);

        $this->assertSame('flow.execution.max_iterations', $definition->key);
        $this->assertSame(PlatformSettingType::Integer, $definition->type);
        $this->assertSame(100, $definition->default);
        $this->assertSame('Maximum iterations', $definition->label);
        $this->assertSame('Flow', $definition->group);
        $this->assertSame('iterations', $definition->unit);
        $this->assertSame(10, $definition->min);
        $this->assertSame(10000, $definition->max);
        $this->assertSame([], $definition->choices);
        $this->assertFalse($definition->hostModeOnly);
    }

    /**
     * @param  callable(): PlatformSettingDefinition  $make
     */
    #[DataProvider('invalid')]
    public function test_an_invalid_definition_is_refused(callable $make): void
    {
        $this->expectException(InvalidArgumentException::class);

        $make();
    }

    public function test_an_integer_accepts_whole_numbers_within_its_bounds(): void
    {
        $definition = $this->integer(min: 10, max: 100, default: 50);

        $this->assertNull($definition->violation(10));
        $this->assertNull($definition->violation(100));
        $this->assertSame('must be at least 10.', $definition->violation(9));
        $this->assertSame('must be at most 100.', $definition->violation(101));
        $this->assertSame('must be a whole number.', $definition->violation('50'));
        $this->assertSame('must be a whole number.', $definition->violation(50.0));
    }

    public function test_a_boolean_accepts_only_booleans(): void
    {
        $definition = new PlatformSettingDefinition('conversation.enabled', PlatformSettingType::Boolean, true, 'L', 'D', 'G');

        $this->assertNull($definition->violation(false));
        $this->assertSame('must be true or false.', $definition->violation(1));
    }

    public function test_a_text_accepts_strings_up_to_its_length(): void
    {
        $definition = new PlatformSettingDefinition('brand.name', PlatformSettingType::Text, 'FaPost', 'L', 'D', 'G', max: 6);

        $this->assertNull($definition->violation('Fapost'));
        $this->assertSame('must be at most 6 characters.', $definition->violation('FaPost!'));
        $this->assertSame('must be text.', $definition->violation(1));
    }

    public function test_a_choice_accepts_only_its_choices(): void
    {
        $definition = new PlatformSettingDefinition('ui.mode', PlatformSettingType::Choice, 'a', 'L', 'D', 'G', choices: ['a', 'b']);

        $this->assertNull($definition->violation('b'));
        $this->assertSame('must be one of: a, b.', $definition->violation('c'));
    }

    public function test_the_type_values_are_stable(): void
    {
        $this->assertSame(['integer', 'boolean', 'text', 'choice'], array_map(static fn (PlatformSettingType $t): string => $t->value, PlatformSettingType::cases()));
    }

    private function integer(?int $min, ?int $max, int $default): PlatformSettingDefinition
    {
        return new PlatformSettingDefinition(
            key: 'flow.execution.max_iterations',
            type: PlatformSettingType::Integer,
            default: $default,
            label: 'Maximum iterations',
            description: 'How many steps one flow run may take.',
            group: 'Flow',
            unit: 'iterations',
            min: $min,
            max: $max,
        );
    }
}
