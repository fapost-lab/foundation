<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Console;

use Closure;
use Fapost\Foundation\Console\DTO\ColumnContext;
use Fapost\Foundation\Console\DTO\ConsoleApplication;
use Fapost\Foundation\Console\DTO\ContextAction;
use Fapost\Foundation\Console\DTO\DashboardWidget;
use Fapost\Foundation\Console\DTO\MenuItem;
use Fapost\Foundation\Console\DTO\SettingsTab;
use Fapost\Foundation\Console\DTO\ShellElement;
use Fapost\Foundation\Console\DTO\TableColumn;
use Fapost\Foundation\Console\Enums\CellType;
use Fapost\Foundation\Console\Enums\ConsoleTarget;
use Fapost\Foundation\Console\Enums\ContextActionKind;
use Fapost\Foundation\Console\Enums\DashboardTarget;
use Fapost\Foundation\Console\Enums\SettingsTarget;
use Fapost\Foundation\Console\Enums\ShellSlot;
use Fapost\Foundation\Console\Enums\TableTarget;
use Fapost\Foundation\Console\Enums\Tone;
use Fapost\Foundation\Console\Enums\WidgetSize;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

final class ConsoleDeclarationTest extends TestCase
{
    private const string VALUES = 'Acme\\Feedback\\ScoreValues';

    /**
     * @return array<string, array{Closure(): mixed, string}>
     */
    public static function invalidDeclarations(): array
    {
        return [
            'application label without namespace' => [static fn () => new ConsoleApplication('Feedback', 'star', 'solutions.feedback.index'), 'label "Feedback" must be a translation key'],
            'application blank label'             => [static fn () => new ConsoleApplication('', 'star', 'solutions.feedback.index'), 'must be a translation key'],
            'application icon in PascalCase'      => [static fn () => new ConsoleApplication('feedback::app', 'Star', 'solutions.feedback.index'), 'icon "Star" must be a lucide icon name'],
            'application route without prefix'    => [static fn () => new ConsoleApplication('feedback::app', 'star', 'feedback.index'), 'route "feedback.index" must be a route name of the form "solutions.<id>.<name>"'],
            'application blank ability'           => [static fn () => new ConsoleApplication('feedback::app', 'star', 'solutions.feedback.index', ability: ' '), 'ability must be a non-empty'],
            'application short label'             => [static fn () => new ConsoleApplication('feedback::app', 'star', 'solutions.feedback.index', shortLabel: 'FB'), 'label "FB" must be a translation key'],
            'application menu of strings'         => [static fn () => new ConsoleApplication('feedback::app', 'star', 'solutions.feedback.index', ['solutions.feedback.index']), 'menu must hold only'],
            'application menu keyed'              => [static fn () => new ConsoleApplication('feedback::app', 'star', 'solutions.feedback.index', ['a' => self::menuItem()]), 'menu must be a list'],
            'menu item route'                     => [static fn () => new MenuItem('feedback::menu.reviews', 'solutions.feedback'), 'route "solutions.feedback" must be a route name'],
            'menu item label'                     => [static fn () => new MenuItem('Reviews', 'solutions.feedback.reviews'), 'label "Reviews" must be a translation key'],
            'menu item icon'                      => [static fn () => new MenuItem('feedback::menu.reviews', 'solutions.feedback.reviews', 'star_full'), 'icon "star_full"'],
            'menu item group'                     => [static fn () => new MenuItem('feedback::menu.reviews', 'solutions.feedback.reviews', group: 'Reports'), 'group label "Reports"'],
            'link key uppercase'                  => [static fn () => ContextAction::link(ConsoleTarget::ContactView, 'Rate', 'feedback::rate', 'solutions.feedback.rate'), 'key "Rate" must match'],
            'link label'                          => [static fn () => ContextAction::link(ConsoleTarget::ContactView, 'rate', 'Rate', 'solutions.feedback.rate'), 'label "Rate"'],
            'link route'                          => [static fn () => ContextAction::link(ConsoleTarget::ContactView, 'rate', 'feedback::rate', 'rate'), 'route "rate"'],
            'link icon'                           => [static fn () => ContextAction::link(ConsoleTarget::ContactView, 'rate', 'feedback::rate', 'solutions.feedback.rate', 'star '), 'icon "star "'],
            'dialog component without prefix'     => [static fn () => ContextAction::dialog(ConsoleTarget::ContactRow, 'rate', 'feedback::rate', 'RateDialog'), 'component "RateDialog" must be "<id>::<Path>"'],
            'dialog component lowercase path'     => [static fn () => ContextAction::dialog(ConsoleTarget::ContactRow, 'rate', 'feedback::rate', 'feedback::rate'), 'component "feedback::rate"'],
            'dialog ability'                      => [static fn () => ContextAction::dialog(ConsoleTarget::ContactRow, 'rate', 'feedback::rate', 'feedback::Rate', ability: ''), 'ability must be a non-empty'],
            'shell key'                           => [static fn () => new ShellElement(ShellSlot::Floating, 'chat-bubble', 'feedback::Bubble'), 'key "chat-bubble" must match'],
            'shell component'                     => [static fn () => new ShellElement(ShellSlot::Header, 'bubble', 'feedback::Bubble/'), 'component "feedback::Bubble/"'],
            'widget key'                          => [static fn () => new DashboardWidget(DashboardTarget::Console, '', 'feedback::Score'), 'key "" must match'],
            'widget component'                    => [static fn () => new DashboardWidget(DashboardTarget::Admin, 'score', 'Score'), 'component "Score"'],
            'tab key'                             => [static fn () => new SettingsTab(SettingsTarget::Tenant, '1st', 'feedback::settings', 'feedback::Settings'), 'key "1st" must match'],
            'tab label'                           => [static fn () => new SettingsTab(SettingsTarget::Tenant, 'feedback', 'Feedback', 'feedback::Settings'), 'label "Feedback"'],
            'tab component'                       => [static fn () => new SettingsTab(SettingsTarget::Assistant, 'feedback', 'feedback::settings', 'feedback::settings'), 'component "feedback::settings"'],
            'column key'                          => [static fn () => new TableColumn(TableTarget::Contacts, 'Score', 'feedback::score', CellType::Number, self::VALUES), 'key "Score" must match'],
            'column label'                        => [static fn () => new TableColumn(TableTarget::Contacts, 'score', 'Score', CellType::Number, self::VALUES), 'label "Score"'],
            'column values leading backslash'     => [static fn () => new TableColumn(TableTarget::Contacts, 'score', 'feedback::score', CellType::Number, '\\' . self::VALUES), 'must be a fully qualified class name'],
            'column values not a class'           => [static fn () => new TableColumn(TableTarget::Contacts, 'score', 'feedback::score', CellType::Number, 'score values'), 'must be a fully qualified class name'],
            'column tone not a Tone'              => [static fn () => new TableColumn(TableTarget::Conversations, 'state', 'feedback::state', CellType::Status, self::VALUES, tones: ['open' => 'success']), 'tones must map non-empty values'],
            'column tone for an empty value'      => [static fn () => new TableColumn(TableTarget::Conversations, 'state', 'feedback::state', CellType::Status, self::VALUES, tones: ['' => Tone::Neutral]), 'tones must map non-empty values'],
            'column context key'                  => [static fn () => new ColumnContext(TableTarget::Contacts, 'Score', null), 'key "Score" must match'],
            'column context empty assistant'      => [static fn () => new ColumnContext(TableTarget::Contacts, 'score', ''), 'assistant id must be null or non-empty'],
        ];
    }

    /**
     * @param  Closure(): mixed  $declare
     */
    #[DataProvider('invalidDeclarations')]
    public function test_a_malformed_declaration_is_refused_with_the_rule_it_breaks(Closure $declare, string $expected): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expected);

        $declare();
    }

    public function test_an_application_keeps_what_it_was_given(): void
    {
        $item        = self::menuItem();
        $application = new ConsoleApplication(
            'feedback::console.app',
            'message-circle',
            'solutions.feedback.index',
            [$item],
            perAssistant: true,
            ability: 'feedback.view',
            shortLabel: 'feedback::console.short',
        );

        $this->assertSame([$item], $application->menu);
        $this->assertTrue($application->perAssistant);
        $this->assertSame('feedback.view', $application->ability);
        $this->assertSame('feedback::console.short', $application->shortLabel);
        $this->assertSame('circle', $item->icon);
        $this->assertSame('feedback::console.menu.reports', $item->group);
    }

    public function test_an_application_defaults_to_tenant_wide_without_a_menu(): void
    {
        $application = new ConsoleApplication('feedback::console.app', 'star', 'solutions.feedback.index');

        $this->assertSame([], $application->menu);
        $this->assertFalse($application->perAssistant);
        $this->assertNull($application->ability);
        $this->assertNull($application->shortLabel);
    }

    public function test_a_link_action_carries_a_route_and_no_component(): void
    {
        $action = ContextAction::link(ConsoleTarget::ContactView, 'rate', 'feedback::actions.rate', 'solutions.feedback.rate', 'star', 'feedback.rate', 5);

        $this->assertSame(ContextActionKind::Link, $action->kind);
        $this->assertSame(ConsoleTarget::ContactView, $action->target);
        $this->assertSame('rate', $action->key);
        $this->assertSame('feedback::actions.rate', $action->label);
        $this->assertSame('solutions.feedback.rate', $action->route);
        $this->assertNull($action->component);
        $this->assertSame('star', $action->icon);
        $this->assertSame('feedback.rate', $action->ability);
        $this->assertSame(5, $action->order);
    }

    public function test_a_dialog_action_carries_a_component_and_no_route(): void
    {
        $action = ContextAction::dialog(ConsoleTarget::ConversationView, 'rate', 'feedback::actions.rate', 'feedback::Dialogs/Rate');

        $this->assertSame(ContextActionKind::Dialog, $action->kind);
        $this->assertSame('feedback::Dialogs/Rate', $action->component);
        $this->assertNull($action->route);
        $this->assertSame('circle', $action->icon);
        $this->assertNull($action->ability);
        $this->assertSame(0, $action->order);
    }

    public function test_a_context_action_is_built_only_through_its_named_constructors(): void
    {
        $this->assertTrue((new ReflectionMethod(ContextAction::class, '__construct'))->isPrivate());
    }

    public function test_the_other_points_keep_their_defaults(): void
    {
        $element = new ShellElement(ShellSlot::Floating, 'bubble', 'feedback::Bubble');
        $widget  = new DashboardWidget(DashboardTarget::Console, 'score', 'feedback::Widgets/Score');
        $tab     = new SettingsTab(SettingsTarget::Assistant, 'feedback', 'feedback::settings.tab', 'feedback::Settings');
        $column  = new TableColumn(TableTarget::Contacts, 'score', 'feedback::columns.score', CellType::Number, self::VALUES);

        $this->assertFalse($element->perAssistant);
        $this->assertSame(0, $element->order);
        $this->assertSame(WidgetSize::Single, $widget->size);
        $this->assertNull($tab->ability);
        $this->assertSame([], $column->tones);
        $this->assertSame(self::VALUES, $column->values);
    }

    public function test_a_status_column_keeps_its_tones(): void
    {
        $column = new TableColumn(
            TableTarget::Conversations,
            'state',
            'feedback::columns.state',
            CellType::Status,
            self::VALUES,
            tones: ['open' => Tone::Success, 'late' => Tone::Danger],
        );

        $this->assertSame(['open' => Tone::Success, 'late' => Tone::Danger], $column->tones);
    }

    public function test_a_column_context_names_the_column_and_the_assistant(): void
    {
        $context = new ColumnContext(TableTarget::Conversations, 'state', '01JABCDEF');

        $this->assertSame(TableTarget::Conversations, $context->target);
        $this->assertSame('state', $context->key);
        $this->assertSame('01JABCDEF', $context->assistantId);
        $this->assertNull((new ColumnContext(TableTarget::Contacts, 'score', null))->assistantId);
    }

    private static function menuItem(): MenuItem
    {
        return new MenuItem('feedback::console.menu.reviews', 'solutions.feedback.reviews', group: 'feedback::console.menu.reports');
    }
}
