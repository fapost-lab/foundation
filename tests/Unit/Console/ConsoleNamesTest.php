<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Console;

use Fapost\Foundation\Console\ConsoleNames;
use Fapost\Foundation\Console\Enums\CellType;
use Fapost\Foundation\Console\Enums\ConsoleTarget;
use Fapost\Foundation\Console\Enums\ContextActionKind;
use Fapost\Foundation\Console\Enums\DashboardTarget;
use Fapost\Foundation\Console\Enums\SettingsTarget;
use Fapost\Foundation\Console\Enums\ShellSlot;
use Fapost\Foundation\Console\Enums\TableTarget;
use Fapost\Foundation\Console\Enums\Tone;
use Fapost\Foundation\Console\Enums\WidgetSize;
use Fapost\Foundation\Console\Exceptions\InvalidConsoleDeclarationException;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ConsoleNamesTest extends TestCase
{
    /**
     * @return array<string, array{string, string|null}>
     */
    public static function components(): array
    {
        return [
            'one segment'           => ['feedback::Score', 'feedback'],
            'nested path'           => ['feedback::Widgets/Score', 'feedback'],
            'digits and underscore' => ['hr_2::Pages/Index2', 'hr_2'],
            'no prefix'             => ['Widgets/Score', null],
            'one-letter id'         => ['f::Score', null],
            'uppercase id'          => ['Feedback::Score', null],
            'lowercase segment'     => ['feedback::widgets/Score', null],
            'trailing slash'        => ['feedback::Score/', null],
            'dash in path'          => ['feedback::My-Score', null],
            'trailing newline'      => ["feedback::Score\n", null],
            'single colon'          => ['feedback:Score', null],
            'empty'                 => ['', null],
        ];
    }

    #[DataProvider('components')]
    public function test_solution_of_reads_the_id_of_a_well_formed_component(string $component, ?string $expected): void
    {
        $this->assertSame($expected, ConsoleNames::solutionOf($component));
    }

    public function test_the_patterns_accept_what_the_documentation_shows(): void
    {
        $this->assertSame(1, preg_match(ConsoleNames::KEY_PATTERN, 'score'));
        $this->assertSame(1, preg_match(ConsoleNames::KEY_PATTERN, 's'));
        $this->assertSame(0, preg_match(ConsoleNames::KEY_PATTERN, 'Score'));
        $this->assertSame(0, preg_match(ConsoleNames::KEY_PATTERN, str_repeat('k', 33)));
        $this->assertSame(1, preg_match(ConsoleNames::LABEL_PATTERN, 'feedback::console.app'));
        $this->assertSame(0, preg_match(ConsoleNames::LABEL_PATTERN, 'console.app'));
        $this->assertSame(1, preg_match(ConsoleNames::ICON_PATTERN, 'message-circle'));
        $this->assertSame(0, preg_match(ConsoleNames::ICON_PATTERN, 'MessageCircle'));
        $this->assertSame(1, preg_match(ConsoleNames::ROUTE_PATTERN, 'solutions.feedback.index'));
        $this->assertSame(0, preg_match(ConsoleNames::ROUTE_PATTERN, 'feedback.index'));
        $this->assertSame(0, preg_match(ConsoleNames::ROUTE_PATTERN, 'solutions.feedback.'));
        $this->assertStringStartsWith('^' . mb_substr(ConsoleNames::ROUTE_PREFIX, 0, -1), mb_substr(ConsoleNames::ROUTE_PATTERN, 1));
    }

    public function test_the_enum_values_are_what_the_client_and_the_documentation_use(): void
    {
        $values = static fn (array $cases): array => array_map(static fn ($case): string => $case->value, $cases);

        $this->assertSame(['contact.view', 'contact.row', 'conversation.view'], $values(ConsoleTarget::cases()));
        $this->assertSame(['floating', 'header'], $values(ShellSlot::cases()));
        $this->assertSame(['console', 'admin'], $values(DashboardTarget::cases()));
        $this->assertSame(['assistant', 'tenant'], $values(SettingsTarget::cases()));
        $this->assertSame(['contacts', 'conversations'], $values(TableTarget::cases()));
        $this->assertSame(['link', 'dialog'], $values(ContextActionKind::cases()));
        $this->assertSame(['single', 'double', 'full'], $values(WidgetSize::cases()));
        $this->assertSame(['text', 'badge', 'number', 'date', 'status'], $values(CellType::cases()));
        $this->assertSame(['neutral', 'success', 'warning', 'danger', 'info'], $values(Tone::cases()));
    }

    public function test_the_declaration_exception_names_the_solution(): void
    {
        $exception = new InvalidConsoleDeclarationException('feedback', 'Register console UI from boot().');

        $this->assertInstanceOf(LogicException::class, $exception);
        $this->assertSame('feedback', $exception->solutionId);
        $this->assertSame('Register console UI from boot().', $exception->getMessage());
        $this->assertNull((new InvalidConsoleDeclarationException(null, 'No Solution is booting.'))->solutionId);
    }
}
