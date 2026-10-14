<?php

declare(strict_types=1);

namespace Fapost\Foundation\Console\Contracts;

use Closure;
use Fapost\Foundation\Console\DTO\ConsoleApplication;
use Fapost\Foundation\Console\DTO\ContextAction;
use Fapost\Foundation\Console\DTO\DashboardWidget;
use Fapost\Foundation\Console\DTO\SettingsTab;
use Fapost\Foundation\Console\DTO\ShellElement;
use Fapost\Foundation\Console\DTO\TableColumn;
use Fapost\Foundation\Console\Exceptions\InvalidConsoleDeclarationException;
use Illuminate\Routing\Router;

/**
 * Console UI a Solution adds. Resolved from the container in the Solution provider's `boot()`; Core binds every
 * declaration to the id of the Solution whose provider is booting and closes the registry after boot.
 *
 * Write-only: nothing outside Core reads the declarations. Every method throws
 * {@see InvalidConsoleDeclarationException} when called outside the booting provider's `boot()`, with a name
 * outside the Solution's namespace (`<id>::`, `solutions.<id>.`), or with a key the Solution already used.
 *
 * @experimental
 */
interface ConsoleExtensionsInterface
{
    /**
     * The Solution's own application in the app rail. At most one per Solution; its id is the Solution id.
     *
     * @throws InvalidConsoleDeclarationException
     *
     * @experimental
     */
    public function application(ConsoleApplication $application): void;

    /**
     * @throws InvalidConsoleDeclarationException
     *
     * @experimental
     */
    public function contextAction(ContextAction $action): void;

    /**
     * @throws InvalidConsoleDeclarationException
     *
     * @experimental
     */
    public function shellElement(ShellElement $element): void;

    /**
     * @throws InvalidConsoleDeclarationException
     *
     * @experimental
     */
    public function dashboardWidget(DashboardWidget $widget): void;

    /**
     * @throws InvalidConsoleDeclarationException
     *
     * @experimental
     */
    public function settingsTab(SettingsTab $tab): void;

    /**
     * @throws InvalidConsoleDeclarationException
     *
     * @experimental
     */
    public function tableColumn(TableColumn $column): void;

    /**
     * Tenant-wide routes: Core registers them on the panel domain under `apps/<id>/`, names `solutions.<id>.`,
     * middleware group `solution`. Controllers only (route:cache).
     *
     * @param  Closure(Router): void  $routes
     *
     * @throws InvalidConsoleDeclarationException
     *
     * @experimental
     */
    public function routes(Closure $routes): void;

    /**
     * Per-assistant routes: `assistant/{tenant}/apps/<id>/`, the console stack plus `solution`. Names and
     * controllers as in {@see self::routes()}.
     *
     * @param  Closure(Router): void  $routes
     *
     * @throws InvalidConsoleDeclarationException
     *
     * @experimental
     */
    public function assistantRoutes(Closure $routes): void;
}
