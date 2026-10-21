<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Channel\Settings;

use Fapost\Foundation\Channel\Settings\ChannelPublicLink;
use Fapost\Foundation\Channel\Settings\ChannelSettingField;
use Fapost\Foundation\Channel\Settings\ChannelSettingsSchema;
use Fapost\Foundation\Channel\Settings\ChannelSettingType;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ChannelSettingsSchemaTest extends TestCase
{
    public function test_it_splits_editable_and_managed_fields(): void
    {
        $schema = new ChannelSettingsSchema([
            new ChannelSettingField('limit', ChannelSettingType::Integer, 'Limit'),
            new ChannelSettingField('bot', ChannelSettingType::Text, 'Bot', managed: true),
        ]);

        $this->assertSame(['limit'], array_map(static fn (ChannelSettingField $f): string => $f->key, $schema->editable()));
        $this->assertSame(['bot'], $schema->managedKeys());
        $this->assertSame('Bot', $schema->field('bot')?->label);
        $this->assertNull($schema->field('missing'));
    }

    public function test_an_empty_schema_uses_a_secret_token_without_a_pattern(): void
    {
        $schema = new ChannelSettingsSchema();

        $this->assertSame([], $schema->fields);
        $this->assertTrue($schema->usesSecretToken);
        $this->assertNull($schema->secretTokenPattern);
    }

    public function test_it_rejects_a_repeated_key(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ChannelSettingsSchema([
            new ChannelSettingField('a', ChannelSettingType::Text, 'A'),
            new ChannelSettingField('a', ChannelSettingType::Text, 'A again'),
        ]);
    }

    public function test_it_rejects_a_pattern_that_does_not_compile(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ChannelSettingsSchema(secretTokenPattern: '[unclosed');
    }

    public function test_it_rejects_a_pattern_without_a_secret_token(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ChannelSettingsSchema(usesSecretToken: false, secretTokenPattern: '[a-z]+');
    }

    public function test_a_public_link_needs_a_username_and_a_handle(): void
    {
        $link = new ChannelPublicLink('acme_bot', '@acme_bot', 'https://t.me/acme_bot');
        $this->assertSame('https://t.me/acme_bot', $link->url);

        $this->expectException(InvalidArgumentException::class);
        new ChannelPublicLink('', '@x');
    }
}
