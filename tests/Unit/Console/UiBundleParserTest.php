<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Console;

use Fapost\Foundation\Console\Bundle\UiBundle;
use Fapost\Foundation\Console\Bundle\UiBundleParser;
use Fapost\Foundation\Console\Bundle\UiBundleSchema;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class UiBundleParserTest extends TestCase
{
    /**
     * @return array<string, array{array<mixed>, string}>
     */
    public static function violations(): array
    {
        return [
            'schema missing'               => [self::without('schema'), 'feedback: fapost-ui.json: schema: required'],
            'schema not an integer'        => [self::bundle(['schema' => '1']), 'schema: must be an integer'],
            'schema unsupported'           => [self::bundle(['schema' => 2]), 'schema: 2 is not supported by this fapost/foundation (supports: 1)'],
            'solution missing'             => [self::without('solution'), 'solution: required'],
            'solution of another'          => [self::bundle(['solution' => 'hr']), 'solution: must be "feedback", the id in the manifest'],
            'kit missing'                  => [self::without('kit'), 'kit: required'],
            'kit blank'                    => [self::bundle(['kit' => ' ']), 'kit: must be a non-empty version constraint'],
            'vue not a string'             => [self::bundle(['vue' => 3]), 'vue: must be a non-empty version constraint'],
            'inertia missing'              => [self::without('inertia'), 'inertia: required'],
            'pages a list'                 => [self::bundle(['pages' => ['pages/index.js']]), 'pages: must be an object of Path => file'],
            'pages a string'               => [self::bundle(['pages' => 'pages']), 'pages: must be an object'],
            'page key with prefix'         => [self::bundle(['pages' => ['feedback::Index' => 'index.js']]), 'pages.feedback::Index: the key must be PascalCase segments'],
            'page key lowercase'           => [self::bundle(['pages' => ['reviews/Index' => 'index.js']]), 'pages.reviews/Index: the key must be PascalCase'],
            'page file absolute'           => [self::bundle(['pages' => ['Index' => '/var/www/index.js']]), 'pages.Index: must be a path relative to the directory of fapost-ui.json'],
            'page file escaping'           => [self::bundle(['pages' => ['Index' => '../index.js']]), 'pages.Index: must be a path relative'],
            'page file escaping deeper'    => [self::bundle(['pages' => ['Index' => 'pages/../../index.js']]), 'pages.Index: must be a path relative'],
            'page file not a script'       => [self::bundle(['pages' => ['Index' => 'pages/index.ts']]), 'pages.Index: must be a path relative to the directory of fapost-ui.json of segments made of letters, digits, ".", "_" and "-", without "." or ".." segments, ending with ".js"'],
            'page file a url'              => [self::bundle(['pages' => ['Index' => 'https://cdn.example/index.js']]), 'pages.Index: must be a path relative'],
            'page file with backslash'     => [self::bundle(['pages' => ['Index' => 'pages\\index.js']]), 'pages.Index: must be a path relative'],
            'page file only an extension'  => [self::bundle(['pages' => ['Index' => '.js']]), 'pages.Index: must be a path relative'],
            'page file not a string'       => [self::bundle(['pages' => ['Index' => ['index.js']]]), 'pages.Index: must be a path relative'],
            'page file with a dot segment' => [self::bundle(['pages' => ['Index' => './index.js']]), 'pages.Index: must be a path relative'],
            'page file with a colon'       => [self::bundle(['pages' => ['Index' => 'c:index.js']]), 'pages.Index: must be a path relative'],
            'page file with a space'       => [self::bundle(['pages' => ['Index' => 'pages/my index.js']]), 'pages.Index: must be a path relative'],
            'page file with a tab'         => [self::bundle(['pages' => ['Index' => "pages/index\t.js"]]), 'pages.Index: must be a path relative'],
            'page file with a percent'     => [self::bundle(['pages' => ['Index' => 'pages/%2e%2e/index.js']]), 'pages.Index: must be a path relative'],
            'page file with a query'       => [self::bundle(['pages' => ['Index' => 'pages/index.js?v=1.js']]), 'pages.Index: must be a path relative'],
            'page file with a fragment'    => [self::bundle(['pages' => ['Index' => 'pages/index#x.js']]), 'pages.Index: must be a path relative'],
            'page file with empty segment' => [self::bundle(['pages' => ['Index' => 'pages//index.js']]), 'pages.Index: must be a path relative'],
            'path both page and component' => [self::bundle(['pages' => ['Score' => 'pages/score.js'], 'components' => ['Score' => 'components/score.js']]), 'components.Score: "Score" is also listed under pages; a Path names one page or one component'],
            'component key'                => [self::bundle(['components' => ['Widgets/' => 'score.js']]), 'components.Widgets/: the key must be PascalCase'],
            'component file'               => [self::bundle(['components' => ['Score' => 'score.css']]), 'components.Score: must be a path relative'],
            'css an object'                => [self::bundle(['css' => ['main' => 'app.css']]), 'css: must be a list of files'],
            'css file not a stylesheet'    => [self::bundle(['css' => ['assets/app.css', 'assets/app.js']]), 'css[1]: must be a path relative to the directory of fapost-ui.json of segments made of letters, digits, ".", "_" and "-", without "." or ".." segments, ending with ".css"'],
            'css file escaping'            => [self::bundle(['css' => ['../app.css']]), 'css[0]: must be a path relative'],
            'nothing to load'              => [self::bundle(['pages' => [], 'components' => []]), 'pages: pages and components together must list at least one entry'],
            'unknown field'                => [self::bundle(['routes' => []]), 'feedback: fapost-ui.json: routes: unknown field (schema 1 allows: schema, solution, kit, vue, inertia, pages, components, css); a newer fapost/foundation may be required'],
            'a list at the root'           => [[1, 2], 'feedback: fapost-ui.json: (root): must be an object'],
        ];
    }

    public function test_a_valid_file_becomes_a_bundle(): void
    {
        $result = (new UiBundleParser())->parse(self::bundle(), 'feedback');

        $this->assertTrue($result->isValid());
        $this->assertSame([], $result->lines());
        $this->assertTrue((new UiBundleParser())->parse(self::bundle(['pages' => ['Index' => 'assets/index-B1x_9.min.js']]), 'feedback')->isValid());

        $bundle = $result->bundle;
        $this->assertInstanceOf(UiBundle::class, $bundle);
        $this->assertSame(1, $bundle->schema);
        $this->assertSame('feedback', $bundle->solution);
        $this->assertSame('^0.1', $bundle->kit);
        $this->assertSame('^3.5', $bundle->vue);
        $this->assertSame('^3.0', $bundle->inertia);
        $this->assertSame(['Reviews/Index' => 'pages/reviews-index.js'], $bundle->pages);
        $this->assertSame(['Widgets/Score' => 'components/score.js', 'Dialogs/Rate' => 'components/rate.js'], $bundle->components);
        $this->assertSame(['assets/feedback.css'], $bundle->css);
    }

    public function test_pages_components_and_css_are_optional_as_long_as_something_is_listed(): void
    {
        $onlyComponents = self::bundle();
        unset($onlyComponents['pages'], $onlyComponents['css']);

        $bundle = (new UiBundleParser())->parse($onlyComponents, 'feedback')->bundle;

        $this->assertInstanceOf(UiBundle::class, $bundle);
        $this->assertSame([], $bundle->pages);
        $this->assertSame([], $bundle->css);

        $nothing = self::bundle();
        unset($nothing['pages'], $nothing['components']);

        $this->assertSame(
            ['feedback: fapost-ui.json: pages: pages and components together must list at least one entry'],
            (new UiBundleParser())->parse($nothing, 'feedback')->lines(),
        );
    }

    public function test_extension_keys_are_ignored(): void
    {
        $this->assertTrue((new UiBundleParser())->parse(self::bundle(['x-build' => ['vite' => '8']]), 'feedback')->isValid());
    }

    /**
     * @param  array<mixed>  $decoded
     */
    #[DataProvider('violations')]
    public function test_a_rule_breach_names_the_solution_and_the_field(array $decoded, string $expected): void
    {
        $result = (new UiBundleParser())->parse($decoded, 'feedback');
        $lines  = $result->lines();

        $this->assertNull($result->bundle);
        $this->assertFalse($result->isValid());
        $this->assertTrue(
            [] !== array_filter($lines, static fn (string $line): bool => str_contains($line, $expected)),
            "Expected a line containing:\n{$expected}\nGot:\n" . implode("\n", $lines),
        );
    }

    public function test_every_violation_is_collected_in_one_pass(): void
    {
        $lines = (new UiBundleParser())->parse(self::bundle([
            'solution'   => 'hr',
            'kit'        => '',
            'pages'      => ['index' => 'index.js', 'Index' => '../index.js'],
            'components' => ['Score' => 'score.vue'],
            'css'        => ['/app.css'],
            'routes'     => [],
        ]), 'feedback')->lines();

        $this->assertCount(7, $lines, implode("\n", $lines));
    }

    public function test_an_unsupported_schema_is_reported_without_judging_the_other_fields(): void
    {
        $lines = (new UiBundleParser())->parse(self::bundle(['schema' => 2, 'solution' => 'hr', 'routes' => []]), 'feedback')->lines();

        $this->assertSame(['feedback: fapost-ui.json: schema: 2 is not supported by this fapost/foundation (supports: 1)'], $lines);
    }

    public function test_the_schema_description_covers_every_field_the_parser_accepts(): void
    {
        $described = array_keys(UiBundleSchema::describe());

        foreach (UiBundleSchema::FIELDS as $field) {
            $this->assertContains($field, $described);
        }

        $this->assertContains(UiBundleSchema::CURRENT, UiBundleSchema::SUPPORTED);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private static function bundle(array $overrides = []): array
    {
        return array_replace([
            'schema'     => 1,
            'solution'   => 'feedback',
            'kit'        => '^0.1',
            'vue'        => '^3.5',
            'inertia'    => '^3.0',
            'pages'      => ['Reviews/Index' => 'pages/reviews-index.js'],
            'components' => ['Widgets/Score' => 'components/score.js', 'Dialogs/Rate' => 'components/rate.js'],
            'css'        => ['assets/feedback.css'],
        ], $overrides);
    }

    /**
     * @return array<string, mixed>
     */
    private static function without(string $field): array
    {
        $bundle = self::bundle();
        unset($bundle[$field]);

        return $bundle;
    }
}
