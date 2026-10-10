<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Solution;

use Fapost\Foundation\Solution\Manifest\InvalidManifestException;
use Fapost\Foundation\Solution\Manifest\ManifestParser;
use Fapost\Foundation\Solution\Manifest\ManifestSchema;
use Fapost\Foundation\Solution\Manifest\SolutionManifest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ManifestParserTest extends TestCase
{
    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function violations(): array
    {
        return [
            'id missing'                            => [self::without('id'), 'acme/fapost-feedback: extra.fapost.id: required'],
            'id uppercase'                          => [self::composer(['id' => 'Feedback']), 'acme/fapost-feedback: extra.fapost.id: must match ^[a-z][a-z0-9_]{1,31}$'],
            'id one letter'                         => [self::composer(['id' => 'f']), 'extra.fapost.id: must match'],
            'id too long'                           => [self::composer(['id' => str_repeat('a', 33)]), 'extra.fapost.id: must match'],
            'id with dash'                          => [self::composer(['id' => 'my-id']), 'extra.fapost.id: must match'],
            'id not a string'                       => [self::composer(['id' => 7]), 'extra.fapost.id: must match'],
            'id reserved'                           => [self::composer(['id' => 'platform', 'actions' => []]), 'extra.fapost.id: "platform" is reserved'],
            'id with trailing newline'              => [self::composer(['id' => "feedback\n"]), 'extra.fapost.id: must match'],
            'reserved id with trailing newline'     => [self::composer(['id' => "core\n", 'actions' => []]), 'extra.fapost.id: must match'],
            'id with trailing space'                => [self::composer(['id' => 'feedback ']), 'extra.fapost.id: must match'],
            'provider with trailing newline'        => [self::composer(['provider' => "Acme\\P\n"]), 'extra.fapost.provider: must be a fully qualified class name'],
            'provider with trailing space'          => [self::composer(['provider' => 'Acme\\P ']), 'extra.fapost.provider: must be a fully qualified class name'],
            'action with trailing newline'          => [self::composer(['actions' => ["feedback.submit\n"]]), 'extra.fapost.actions[0]: "feedback.submit'],
            'action with trailing space'            => [self::composer(['actions' => ['feedback.submit ']]), 'extra.fapost.actions[0]'],
            'name missing'                          => [self::without('name'), 'extra.fapost.name: required'],
            'name blank'                            => [self::composer(['name' => '  ']), 'extra.fapost.name: must be a single line'],
            'name too long'                         => [self::composer(['name' => str_repeat('n', 61)]), 'extra.fapost.name: must be a single line of 1 to 60'],
            'name multiline'                        => [self::composer(['name' => "A\nB"]), 'extra.fapost.name: must be a single line'],
            'description too long'                  => [self::composer(['description' => str_repeat('d', 281)]), 'extra.fapost.description: must be a string of up to 280'],
            'provider missing'                      => [self::without('provider'), 'extra.fapost.provider: required'],
            'provider leading backslash'            => [self::composer(['provider' => '\\Acme\\P']), 'extra.fapost.provider: must be a fully qualified class name'],
            'provider not a class name'             => [self::composer(['provider' => 'not a class']), 'extra.fapost.provider: must be a fully qualified class name'],
            'action without prefix'                 => [self::composer(['actions' => ['score']]), 'extra.fapost.actions[0]: "score" must start with "feedback."'],
            'action of another solution'            => [self::composer(['actions' => ['feedback.ok', 'hr.create']]), 'extra.fapost.actions[1]: "hr.create" must start with "feedback."'],
            'action with core prefix'               => [self::composer(['actions' => ['core.send']]), 'extra.fapost.actions[0]: "core.send" must start with "feedback."'],
            'action uppercase'                      => [self::composer(['actions' => ['feedback.Submit']]), 'extra.fapost.actions[0]: "feedback.Submit" must be dot-separated lowercase words'],
            'action empty tail'                     => [self::composer(['actions' => ['feedback.']]), 'extra.fapost.actions[0]'],
            'action duplicated'                     => [self::composer(['actions' => ['feedback.a', 'feedback.a']]), 'extra.fapost.actions[1]: "feedback.a" is listed twice'],
            'action not a string'                   => [self::composer(['actions' => [1]]), 'extra.fapost.actions[0]: must be a string'],
            'actions not a list'                    => [self::composer(['actions' => ['a' => 'feedback.a']]), 'extra.fapost.actions: must be a list'],
            'unknown field'                         => [self::composer(['migrations' => 'database']), 'acme/fapost-feedback: extra.fapost.migrations: unknown field (schema 1 allows: schema, id, name, description, provider, actions); a newer fapost/foundation may be required'],
            'schema missing'                        => [self::without('schema'), 'extra.fapost.schema: required'],
            'schema not an integer'                 => [self::composer(['schema' => '1']), 'extra.fapost.schema: must be an integer'],
            'schema unsupported'                    => [self::composer(['schema' => 2]), 'extra.fapost.schema: 2 is not supported by this fapost/foundation (supports: 1)'],
            'wrong type'                            => [self::composer([], ['type' => 'library']), 'acme/fapost-feedback: type: must be "fapost-solution"'],
            'foundation not required'               => [self::composer([], ['require' => ['fapost/foundation' => null]]), 'require.fapost/foundation: a Solution must require fapost/foundation'],
            'core required'                         => [self::composer([], ['require' => ['fapost/core' => '^1.0']]), 'require.fapost/core: a Solution depends on fapost/foundation, never on Core'],
            'provider listed for Laravel discovery' => [
                self::composer([], ['extra' => ['laravel' => ['providers' => ['Acme\\Feedback\\FeedbackServiceProvider']]]]),
                "extra.laravel.providers: a Solution's provider is booted by Core; remove this block",
            ],
            'another provider listed' => [
                self::composer([], ['extra' => ['laravel' => ['providers' => ['Other\\Provider']]]]),
                'extra.laravel.providers: a Solution\'s provider is booted by Core',
            ],
            'provider listed with a leading backslash' => [
                self::composer([], ['extra' => ['laravel' => ['providers' => ['\\Acme\\Feedback\\FeedbackServiceProvider']]]]),
                'extra.laravel.providers: a Solution\'s provider is booted by Core',
            ],
            'no fapost block' => [['name' => 'acme/x', 'type' => 'fapost-solution', 'require' => ['fapost/foundation' => '^0.13']], 'acme/x: extra.fapost: required'],
        ];
    }

    public function test_a_valid_manifest_becomes_a_value_object(): void
    {
        $result = (new ManifestParser())->parse(self::composer());

        $this->assertTrue($result->isValid());
        $this->assertSame([], $result->violations);

        $manifest = $result->manifest;
        $this->assertInstanceOf(SolutionManifest::class, $manifest);
        $this->assertSame('acme/fapost-feedback', $manifest->package);
        $this->assertSame(1, $manifest->schema);
        $this->assertSame('feedback', $manifest->id);
        $this->assertSame('Feedback', $manifest->name);
        $this->assertSame('Collect feedback.', $manifest->description);
        $this->assertSame('Acme\\Feedback\\FeedbackServiceProvider', $manifest->provider);
        $this->assertSame(['feedback.submit', 'feedback.score'], $manifest->actions);
        $this->assertSame('^0.13', $manifest->foundationConstraint);
    }

    public function test_optional_fields_default(): void
    {
        $composer = self::composer();
        unset($composer['extra']['fapost']['description'], $composer['extra']['fapost']['actions']);

        $manifest = SolutionManifest::fromComposer($composer);

        $this->assertNull($manifest->description);
        $this->assertSame([], $manifest->actions);
    }

    public function test_an_empty_laravel_providers_list_is_allowed(): void
    {
        $this->assertSame([], self::lines(self::composer([], ['extra' => ['laravel' => ['providers' => []]]])));
        $this->assertSame([], self::lines(self::composer([], ['extra' => ['laravel' => ['dont-discover' => ['x/y']]]])));
    }

    public function test_extension_keys_are_ignored(): void
    {
        $this->assertSame([], self::lines(self::composer(['x-tooling' => ['anything' => true]])));
    }

    public function test_from_composer_throws_with_every_violation(): void
    {
        try {
            SolutionManifest::fromComposer(self::composer(['id' => 'Bad', 'name' => '']));
            $this->fail('An invalid manifest must throw.');
        } catch (InvalidManifestException $e) {
            $this->assertSame('acme/fapost-feedback', $e->package);
            $this->assertCount(2, $e->violations);
            $this->assertStringContainsString('acme/fapost-feedback: extra.fapost.id: must match ^[a-z][a-z0-9_]{1,31}$', $e->getMessage());
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
        $this->assertNull((new ManifestParser())->parse($composer)->manifest);
    }

    public function test_every_violation_is_collected_in_one_pass(): void
    {
        $lines = self::lines(self::composer(
            ['id' => 'Bad', 'name' => '', 'provider' => 'x y', 'actions' => ['nope'], 'migrations' => 'm'],
            ['require' => ['fapost/core' => '*']],
        ));

        $this->assertCount(6, $lines, implode("\n", $lines));
    }

    public function test_an_unsupported_schema_is_reported_without_judging_the_other_fields(): void
    {
        $lines = self::lines(self::composer(['schema' => 2, 'future_field' => true]));

        $this->assertCount(1, $lines);
        $this->assertStringContainsString('extra.fapost.schema', $lines[0]);
    }

    public function test_an_unsupported_schema_is_reported_alone_even_when_the_package_is_otherwise_wrong(): void
    {
        $composer = self::composer(['schema' => 2], ['type' => 'library', 'require' => ['fapost/foundation' => null, 'fapost/core' => '*'], 'extra' => ['laravel' => ['providers' => ['Acme\\Feedback\\FeedbackServiceProvider']]]]);

        $lines = self::lines($composer);

        $this->assertCount(1, $lines, implode("\n", $lines));
        $this->assertStringContainsString('extra.fapost.schema', $lines[0]);
    }

    public function test_an_unnamed_package_is_still_reported(): void
    {
        $composer = self::composer();
        unset($composer['name']);

        $result = (new ManifestParser())->parse($composer);

        $this->assertSame(['(unnamed package): name: required'], $result->lines());
    }

    public function test_every_reserved_id_is_refused_and_a_neighbour_is_not(): void
    {
        foreach (ManifestSchema::RESERVED_IDS as $reserved) {
            $this->assertNotSame([], self::lines(self::composer(['id' => $reserved, 'actions' => []])), $reserved);
        }

        $this->assertSame([], self::lines(self::composer(['id' => 'flows', 'actions' => ['flows.a']])));
    }

    public function test_the_schema_description_covers_every_field_the_parser_accepts(): void
    {
        $described = array_keys(ManifestSchema::describe());

        foreach (ManifestSchema::FIELDS as $field) {
            $this->assertContains('extra.fapost.' . $field, $described);
        }
    }
    /**
     * @param  array<string, mixed>  $fapost
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private static function composer(array $fapost = [], array $overrides = []): array
    {
        return array_replace_recursive([
            'name'    => 'acme/fapost-feedback',
            'type'    => 'fapost-solution',
            'require' => ['fapost/foundation' => '^0.13'],
            'extra'   => ['fapost' => array_replace([
                'schema'      => 1,
                'id'          => 'feedback',
                'name'        => 'Feedback',
                'description' => 'Collect feedback.',
                'provider'    => 'Acme\\Feedback\\FeedbackServiceProvider',
                'actions'     => ['feedback.submit', 'feedback.score'],
            ], $fapost)],
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $composer
     * @return list<string>
     */
    private static function lines(array $composer): array
    {
        return (new ManifestParser())->parse($composer)->lines();
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
