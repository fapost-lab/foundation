<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Connector;

use Fapost\Foundation\Connector\Manifest\ConnectorManifest;
use Fapost\Foundation\Connector\Manifest\ConnectorManifestParser;
use Fapost\Foundation\Connector\Manifest\ConnectorManifestSchema;
use Fapost\Foundation\Solution\Manifest\InvalidManifestException;
use Fapost\Foundation\Solution\Manifest\ManifestSchema;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ConnectorManifestParserTest extends TestCase
{
    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function violations(): array
    {
        return [
            'wrong type'                  => [self::composer([], ['type' => 'fapost-solution']), 'acme/fapost-telegram: type: must be "fapost-connector"'],
            'schema missing'              => [self::without('schema'), 'extra.fapost.schema: required'],
            'schema not an integer'       => [self::composer(['schema' => '1']), 'extra.fapost.schema: must be an integer'],
            'schema unsupported'          => [self::composer(['schema' => 2]), 'extra.fapost.schema: 2 is not supported by this fapost/foundation (supports: 1)'],
            'id missing'                  => [self::without('id'), 'extra.fapost.id: required'],
            'id uppercase'                => [self::composer(['id' => 'Telegram']), 'extra.fapost.id: must match ^[a-z][a-z0-9_]{1,31}$'],
            'id reserved'                 => [self::composer(['id' => 'platform']), 'extra.fapost.id: "platform" is reserved'],
            'name missing'                => [self::without('name'), 'extra.fapost.name: required'],
            'name multiline'              => [self::composer(['name' => "A\nB"]), 'extra.fapost.name: must be a single line'],
            'description too long'        => [self::composer(['description' => str_repeat('d', 281)]), 'extra.fapost.description: must be a string of up to 280'],
            'provider missing'            => [self::without('provider'), 'extra.fapost.provider: required'],
            'provider not a class name'   => [self::composer(['provider' => 'not a class']), 'extra.fapost.provider: must be a fully qualified class name'],
            'channels missing'            => [self::without('channels'), 'acme/fapost-telegram: extra.fapost.channels: required'],
            'channels empty'              => [self::composer(['channels' => []]), 'extra.fapost.channels: must be a non-empty list'],
            'channels not a list'         => [self::composer(['channels' => ['a' => 'telegram']]), 'extra.fapost.channels: must be a non-empty list'],
            'channels a string'           => [self::composer(['channels' => 'telegram']), 'extra.fapost.channels: must be a non-empty list'],
            'channel not a string'        => [self::composer(['channels' => ['telegram', 7]]), 'extra.fapost.channels[1]: must be a string matching ^[a-z][a-z0-9_]{1,31}$'],
            'channel uppercase'           => [self::composer(['channels' => ['Telegram']]), 'extra.fapost.channels[0]: must be a string matching'],
            'channel with dash'           => [self::composer(['channels' => ['tele-gram']]), 'extra.fapost.channels[0]: must be a string matching'],
            'channel with newline'        => [self::composer(['channels' => ["telegram\n"]]), 'extra.fapost.channels[0]: must be a string matching'],
            'channel duplicated'          => [self::composer(['channels' => ['telegram', 'bot', 'telegram']]), 'extra.fapost.channels[2]: "telegram" is listed twice'],
            'unknown field'               => [self::composer(['actions' => []]), 'acme/fapost-telegram: extra.fapost.actions: unknown field (schema 1 allows: schema, id, name, description, provider, channels); a newer fapost/foundation may be required'],
            'foundation not required'     => [self::composer([], ['require' => ['fapost/foundation' => null]]), 'require.fapost/foundation: a connector must require fapost/foundation'],
            'core required'               => [self::composer([], ['require' => ['fapost/core' => '^1.0']]), 'require.fapost/core: a connector depends on fapost/foundation, never on Core'],
            'provider listed for Laravel' => [
                self::composer([], ['extra' => ['laravel' => ['providers' => ['Acme\\Telegram\\TelegramServiceProvider']]]]),
                "extra.laravel.providers: a connector's provider is booted by Core; remove this block",
            ],
            'no fapost block' => [['name' => 'acme/x', 'type' => 'fapost-connector', 'require' => ['fapost/foundation' => '^0.21']], 'acme/x: extra.fapost: required: an object with the connector manifest'],
        ];
    }

    public function test_a_valid_manifest_becomes_a_value_object(): void
    {
        $result = (new ConnectorManifestParser())->parse(self::composer());

        $this->assertTrue($result->isValid());
        $this->assertSame([], $result->violations);

        $manifest = $result->manifest;
        $this->assertInstanceOf(ConnectorManifest::class, $manifest);
        $this->assertSame('acme/fapost-telegram', $manifest->package);
        $this->assertSame(1, $manifest->schema);
        $this->assertSame('telegram', $manifest->id);
        $this->assertSame('Telegram', $manifest->name);
        $this->assertSame('Telegram bots.', $manifest->description);
        $this->assertSame('Acme\\Telegram\\TelegramServiceProvider', $manifest->provider);
        $this->assertSame(['telegram', 'telegram_group'], $manifest->channels);
        $this->assertSame('^0.21', $manifest->foundationConstraint);
    }

    public function test_the_description_is_optional(): void
    {
        $composer = self::composer();
        unset($composer['extra']['fapost']['description']);

        $this->assertNull(ConnectorManifest::fromComposer($composer)->description);
    }

    public function test_extension_keys_are_ignored(): void
    {
        $this->assertSame([], self::lines(self::composer(['x-tooling' => ['anything' => true]])));
    }

    public function test_an_empty_laravel_providers_list_is_allowed(): void
    {
        $this->assertSame([], self::lines(self::composer([], ['extra' => ['laravel' => ['providers' => []]]])));
    }

    public function test_from_composer_throws_with_every_violation(): void
    {
        try {
            ConnectorManifest::fromComposer(self::composer(['id' => 'Bad', 'channels' => []]));
            $this->fail('An invalid manifest must throw.');
        } catch (InvalidManifestException $e) {
            $this->assertSame('acme/fapost-telegram', $e->package);
            $this->assertCount(2, $e->violations);
            $this->assertStringContainsString('acme/fapost-telegram: extra.fapost.id: must match', $e->getMessage());
            $this->assertStringContainsString('acme/fapost-telegram: extra.fapost.channels: must be a non-empty list', $e->getMessage());
        }
    }

    /**
     * @param  array<string, mixed>  $composer
     */
    #[DataProvider('violations')]
    public function test_a_rule_breach_names_the_package_and_the_field(array $composer, string $expected): void
    {
        $lines = self::lines($composer);

        $this->assertNotSame([], $lines);
        $this->assertTrue(
            [] !== array_filter($lines, static fn (string $line): bool => str_contains($line, $expected)),
            "Expected a line containing:\n{$expected}\nGot:\n" . implode("\n", $lines),
        );
        $this->assertNull((new ConnectorManifestParser())->parse($composer)->manifest);
    }

    public function test_every_bad_channel_gets_its_own_violation(): void
    {
        $lines = self::lines(self::composer(['channels' => ['Bad', 'ok', 'ok', 3]]));

        $this->assertCount(3, $lines, implode("\n", $lines));
    }

    public function test_every_violation_is_collected_in_one_pass(): void
    {
        $lines = self::lines(self::composer(
            ['id' => 'Bad', 'name' => '', 'provider' => 'x y', 'channels' => [], 'actions' => []],
            ['require' => ['fapost/core' => '*']],
        ));

        $this->assertCount(6, $lines, implode("\n", $lines));
    }

    public function test_an_unsupported_schema_is_reported_alone(): void
    {
        $composer = self::composer(['schema' => 2], ['type' => 'library', 'require' => ['fapost/foundation' => null, 'fapost/core' => '*']]);

        $lines = self::lines($composer);

        $this->assertCount(1, $lines, implode("\n", $lines));
        $this->assertStringContainsString('extra.fapost.schema', $lines[0]);
    }

    public function test_an_unnamed_package_is_still_reported(): void
    {
        $composer = self::composer();
        unset($composer['name']);

        $this->assertSame(['(unnamed package): name: required'], (new ConnectorManifestParser())->parse($composer)->lines());
    }

    public function test_the_schema_description_covers_every_field_the_parser_accepts(): void
    {
        $described = array_keys(ConnectorManifestSchema::describe());

        foreach (ConnectorManifestSchema::FIELDS as $field) {
            $this->assertContains('extra.fapost.' . $field, $described);
        }

        $this->assertSame('fapost-connector', ManifestSchema::CONNECTOR_PACKAGE_TYPE);
        $this->assertSame('^[a-z][a-z0-9_]{1,31}$', ConnectorManifestSchema::channelTypePattern());
    }

    /**
     * @param  array<string, mixed>  $fapost
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private static function composer(array $fapost = [], array $overrides = []): array
    {
        return array_replace_recursive([
            'name'    => 'acme/fapost-telegram',
            'type'    => 'fapost-connector',
            'require' => ['fapost/foundation' => '^0.21'],
            'extra'   => ['fapost' => array_replace([
                'schema'      => 1,
                'id'          => 'telegram',
                'name'        => 'Telegram',
                'description' => 'Telegram bots.',
                'provider'    => 'Acme\\Telegram\\TelegramServiceProvider',
                'channels'    => ['telegram', 'telegram_group'],
            ], $fapost)],
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $composer
     * @return list<string>
     */
    private static function lines(array $composer): array
    {
        return (new ConnectorManifestParser())->parse($composer)->lines();
    }

    /**
     * @return array<string, mixed>
     */
    private static function without(string $field): array
    {
        $composer = self::composer();
        unset($composer['extra']['fapost'][$field]);

        return $composer;
    }
}
