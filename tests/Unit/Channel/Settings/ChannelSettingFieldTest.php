<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Channel\Settings;

use Fapost\Foundation\Channel\Settings\ChannelSettingField;
use Fapost\Foundation\Channel\Settings\ChannelSettingType;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ChannelSettingFieldTest extends TestCase
{
    /**
     * @return array<string, array{callable(): ChannelSettingField}>
     */
    public static function contradictions(): array
    {
        return [
            'bad key'             => [static fn () => new ChannelSettingField('Bad-Key', ChannelSettingType::Text, 'Label')],
            'empty label'         => [static fn () => new ChannelSettingField('key', ChannelSettingType::Text, ' ')],
            'min above max'       => [static fn () => new ChannelSettingField('key', ChannelSettingType::Integer, 'L', min: 5, max: 1)],
            'min on text'         => [static fn () => new ChannelSettingField('key', ChannelSettingType::Text, 'L', min: 1)],
            'max on boolean'      => [static fn () => new ChannelSettingField('key', ChannelSettingType::Boolean, 'L', max: 1)],
            'choices on text'     => [static fn () => new ChannelSettingField('key', ChannelSettingType::Text, 'L', choices: ['a' => 'A'])],
            'choice without any'  => [static fn () => new ChannelSettingField('key', ChannelSettingType::Choice, 'L')],
            'choice empty label'  => [static fn () => new ChannelSettingField('key', ChannelSettingType::Choice, 'L', choices: ['a' => ''])],
            'managed required'    => [static fn () => new ChannelSettingField('key', ChannelSettingType::Text, 'L', required: true, managed: true)],
            'default wrong type'  => [static fn () => new ChannelSettingField('key', ChannelSettingType::Integer, 'L', default: 'x')],
            'default over max'    => [static fn () => new ChannelSettingField('key', ChannelSettingType::Integer, 'L', default: 9, max: 5)],
            'default not in list' => [static fn () => new ChannelSettingField('key', ChannelSettingType::Choice, 'L', default: 'z', choices: ['a' => 'A'])],
            'default repeats'     => [static fn () => new ChannelSettingField('key', ChannelSettingType::Choices, 'L', default: ['a', 'a'], choices: ['a' => 'A'])],
            'empty translation'   => [static fn () => new ChannelSettingField('key', ChannelSettingType::Text, 'L', translationKey: '')],
        ];
    }

    /**
     * @param  callable(): ChannelSettingField  $build
     */
    #[DataProvider('contradictions')]
    public function test_it_rejects_a_contradictory_declaration(callable $build): void
    {
        $this->expectException(InvalidArgumentException::class);

        $build();
    }

    public function test_it_accepts_a_field_of_every_type(): void
    {
        $fields = [
            new ChannelSettingField('name', ChannelSettingType::Text, 'Name', default: 'x', max: 10),
            new ChannelSettingField('count', ChannelSettingType::Integer, 'Count', default: 3, min: 1, max: 5),
            new ChannelSettingField('on', ChannelSettingType::Boolean, 'On', default: false),
            new ChannelSettingField('mode', ChannelSettingType::Choice, 'Mode', default: 'a', choices: ['a' => 'A', 'b' => 'B']),
            new ChannelSettingField('modes', ChannelSettingType::Choices, 'Modes', default: [], choices: ['a' => 'A']),
            new ChannelSettingField('bot_name', ChannelSettingType::Text, 'Bot', managed: true),
        ];

        $this->assertCount(6, $fields);
        $this->assertTrue($fields[5]->managed);
    }
}
