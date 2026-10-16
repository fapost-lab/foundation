# FaPost Foundation

`fapost/foundation` is the **public contract layer** of the FaPost platform. It holds the
interfaces, DTOs, enums and value objects that Core, Solutions and Plugins agree on. It is the
only package a Solution or Plugin is allowed to depend on to integrate with the platform.

## Design rules

- **No dependency on Core.** `use App\...` is forbidden here. Foundation never references concrete
  Core classes; the implementations live in Core and are bound to these contracts at runtime.
- **Contracts, not logic.** Everything here is an interface, a DTO, an enum, or a small value
  object. No business logic, no persistence, no framework wiring.
- If a contract is needed by an external Solution/Plugin, it belongs here. A pure, reusable
  primitive with no Core coupling belongs in [`fapost/support`](https://github.com/fapost-lab/support). Domain-specific
  code stays in Core.

## Requirements

- PHP `^8.4`
- `illuminate/support` `^11 || ^12`
- `psr/http-message`, `spatie/laravel-data`

## Namespace

```
Fapost\Foundation\   →  src/
```

## What's inside

| Area | Namespace | Purpose |
|------|-----------|---------|
| Extension lifecycle | `Contracts`, `Lifecycle`, `Solution\*`, `Support` | Register Solutions/Plugins against the platform; the Solution manifest (`Solution\Manifest\*`: `ManifestParser`, `SolutionManifest`, `ManifestSchema`) and the read-only catalog of installed Solutions (`Solution\Contracts\InstalledSolutionCatalogInterface`, implemented by Core; `Solution\DTO\InstalledSolutionInfo`), and which installed Solutions a tenant may run (`Solution\Contracts\SolutionEntitlementInterface`, implemented by an operator package; Core entitles every Solution by default; `Solution\DTO\SolutionEntitlement`) |
| Console UI (experimental) | `Console\*` | What a Solution adds to the console: its own application, context actions, shell elements, dashboard widgets, settings tabs and table columns (`Console\Contracts\ConsoleExtensionsInterface`, implemented by Core; `Console\Contracts\ColumnValuesInterface`, implemented by a Solution), with their `DTO`, `Enums`, name formats (`ConsoleNames`) and the `fapost-ui.json` reader (`Console\Bundle\*`) |
| Flow engine | `Flow\*`, `Contracts\NodeHandlerInterface`, `Contracts\DataAccessorInterface` | Node handlers, expression engine, triggers, contact writes, scoped state |
| Messaging | `Messaging\*` | Outbound message senders, delivery results, typing/processing indicators |
| Channels | `Channel\*` | Channel adapters and webhook registration |
| Media | `Media\*` | Channel media upload/download contracts and DTOs |
| Tenancy | `Tenancy\*` | Tenant provisioning (`Contracts\TenantProvisionerInterface`), slug reservation and resumable provisioning by tenant id (`Contracts\TenantReservationInterface`), a read-only tenant directory (`Contracts\TenantDirectoryInterface`), changing a tenant's slug and host (`Contracts\TenantRenamerInterface`), support access for platform operators (`Contracts\SupportAccessInterface`) and a tenant's access mode (`Contracts\TenantAccessModeInterface`, implemented by an operator package; Core answers active by default), with their `DTO`, `Enums` and `Exceptions`; implemented by Core |
| Quota | `Quota\*` | Limit registry (`Contracts\LimitRegistryInterface`, implemented by Core and filled by Core and Solutions at boot), per-tenant limits (`Contracts\TenantLimitsInterface`, implemented by an operator package; Core allows everything by default) and the record check Core and Solutions call before creating a counted record (`Contracts\RecordQuotaInterface`, implemented by Core; `Exceptions\RecordLimitReachedException`), the per-period usage meter (`Contracts\UsageMeterInterface`, implemented by an operator package; Core allows everything by default; `DTO\UsageUnit`, `DTO\UsageDecision` with the optional end of the period on a refusal or a warning, and `UsageDecision::approaching()` for an allowed unit that asks Core to warn the tenant's admins that the limit is close, `Exceptions\VolumeLimitReachedException`), the wording of the notification admins get when a limit refuses work, is filled or is close to being used up (`Contracts\LimitNoticeInterface`, implemented by an operator package; Core writes its own text by default; `DTO\LimitNotice`), and a read of how much of a Records or Bytes limit a tenant uses now (`Contracts\TenantUsageInterface`, implemented by Core, called by an operator package outside the tenant context), and the operator's read of how much of each per-period limit a tenant has used in its current period, with an optional upgrade button, shown on the tenant admin's usage page (`Contracts\PeriodUsageInterface`, implemented by an operator package; Core reports nothing by default; `DTO\PeriodUsage`, `DTO\PeriodUsageReport`), with `DTO\LimitDefinition` and `Enums\LimitKind` |
| RAG | `Contracts\RagAdapterInterface`, `DTO\*Rag*` | Retrieval-augmented generation adapters |
| Analytics | `Analytics\*` | Analytics event DTO, writer contract, event types |
| Inbound/outbound DTOs | `DTO\*` | Incoming/outgoing messages, media, webhook payloads, execution results |

### Extension entry point

An extension never touches `Route`, `Schedule` or `Migrations` directly. It declares intent
through `CoreRegistrarInterface`, whose implementation lives in Core:

```php
protected function registerExtensions(CoreRegistrarInterface $registrar): void
{
    $registrar->registerActionHandler(CreateAssessmentAction::class);
}
```

Core calls `registerExtensionsWith($registrar)` on a Solution's provider once per process, after every
provider's `register()` and before any `boot()`, handing it a registrar of its own: Core checks what a
Solution registers against the Solution's manifest (`extra.fapost.actions`). A Solution registers
**action handlers** (`registerActionHandler`, by class), not node types; `registerNodeHandler` is for
Plugins. The provider's `boot()` only runs `bootSolution()`.

- **Solution** — an external composer package with niche domain logic (HR, Recruitment).
  Extend `Lifecycle\AbstractSolutionServiceProvider`.
- **Plugin** — extends platform capabilities without domain logic (a new channel adapter, a new
  RAG provider). Extend `Lifecycle\AbstractPluginServiceProvider`.

### Solution manifest

A Solution declares itself in its own `composer.json`, as data, so it can be checked without running
the package: `"type": "fapost-solution"` plus an `extra.fapost` block.

```json
{
  "name": "acme/fapost-feedback",
  "type": "fapost-solution",
  "require": { "fapost/foundation": "^0.13" },
  "extra": {
    "fapost": {
      "schema": 1,
      "id": "feedback",
      "name": "Feedback",
      "description": "Collect and route customer feedback from flows.",
      "provider": "Acme\\Feedback\\FeedbackServiceProvider",
      "actions": ["feedback.submit", "feedback.score"]
    }
  }
}
```

| Field | Required | Rule |
|-------|----------|------|
| `type` | yes | `fapost-solution` (`fapost-plugin` is reserved) |
| `extra.fapost.schema` | yes | integer; `1` is the only supported number |
| `extra.fapost.id` | yes | `^[a-z][a-z0-9_]{1,31}$`; not `core`, `platform`, `fapost`, `app`, `system`, `flow`, `module`, `rag`, `solution`, `solutions`, `console`, `admin`. **Never changes**: a new id is a different Solution |
| `extra.fapost.name` | yes | one line, 1 to 60 characters, English |
| `extra.fapost.description` | no | up to 280 characters |
| `extra.fapost.provider` | yes | class name of a provider extending `Lifecycle\AbstractSolutionServiceProvider`; Core boots it |
| `extra.laravel.providers` | forbidden | any entry is a violation (an empty list is fine): Laravel's package discovery would boot a Solution's provider whether or not its manifest holds up |
| `extra.fapost.actions` | no | unique action ids, each starting with `<id>.` |
| `extra.fapost.ui` | no | path of the Solution's `fapost-ui.json` relative to the package root: `/`-separated segments of letters, digits, `.`, `_`, `-`, none starting with `.`, ending with `.json`; since foundation 0.15, experimental (see [Console UI](#console-ui-experimental)) |
| `extra.fapost.x-*` | no | ignored; for the author's own tooling |
| `require.fapost/foundation` | yes | the Foundation range the Solution needs; Composer enforces it at install time |
| `require.fapost/core` | forbidden | extensions depend on contracts, never on Core |

The version is the package version Composer already knows; it is not repeated in the manifest.

`Solution\Manifest\ManifestParser` checks a decoded `composer.json` and returns every violation, not
the first, as `<package>: <field>: <message>` lines. It is pure (no file access, no class loading),
so an extension's CI can run it without Core:

```php
$result = (new ManifestParser())->parse(json_decode(file_get_contents('composer.json'), true));

$result->isValid();   // bool
$result->lines();     // ["acme/fapost-feedback: extra.fapost.id: required", ...]
$result->manifest;    // ?SolutionManifest
```

`SolutionManifest::fromComposer()` does the same and throws `InvalidManifestException` instead.
`ManifestSchema::describe()` returns the rules above as data.

**Evolution.** Unknown fields are an error (except `x-*`), so a Solution written for a newer
Foundation never installs silently without part of its behaviour. A new optional field is additive:
same `schema`, a minor Foundation release, and the Solution that uses it requires that Foundation
version, which Composer enforces. A breaking change (meaning, removal, a new required field) is a new
`schema` number; Foundation reads schema N and N-1 within one major version, N-1 as deprecated, and
refuses anything else.

### Console UI (experimental)

> **Experimental since 0.15.** Every type under `Console\` is marked `@experimental`: it may change in a
> minor release, with a changelog entry. Build on it knowing that.

A Solution adds UI to the console in two halves: declarations in PHP, made from its provider's `boot()`,
and a built bundle of Vue pages and components described by `fapost-ui.json`.

**Declarations.** Resolve `Console\Contracts\ConsoleExtensionsInterface` in `boot()` (override it and call
`parent::boot()`). Core binds every declaration to the Solution whose provider is booting and closes the
registry once the application has booted; a call outside that window, a name outside the Solution's
namespace, a duplicate key or a second application throws `Console\Exceptions\InvalidConsoleDeclarationException`.

```php
public function boot(): void
{
    parent::boot();

    $console = $this->app->make(ConsoleExtensionsInterface::class);

    $console->application(new ConsoleApplication(
        label: 'feedback::console.app',
        icon: 'message-circle',
        entryRoute: 'solutions.feedback.index',
        menu: [new MenuItem('feedback::console.menu.reviews', 'solutions.feedback.reviews')],
    ));
    $console->contextAction(ContextAction::dialog(ConsoleTarget::ContactView, 'rate', 'feedback::actions.rate', 'feedback::Dialogs/Rate'));
    $console->dashboardWidget(new DashboardWidget(DashboardTarget::Console, 'score', 'feedback::Widgets/Score', WidgetSize::Double));
    $console->tableColumn(new TableColumn(TableTarget::Contacts, 'score', 'feedback::columns.score', CellType::Number, ScoreValues::class));
    $console->routes(function (Router $router): void {
        $router->get('/', [ReviewController::class, 'index'])->name('index');
    });
}
```

| Point | DTO | Targets (value sent to the client) |
|-------|-----|------------------------------------|
| Own application in the app rail | `ConsoleApplication`, `MenuItem` | — (at most one; its id is the Solution id) |
| Context action | `ContextAction::link()` / `::dialog()` | `ConsoleTarget`: `contact.view`, `contact.row`, `conversation.view` |
| Shell element | `ShellElement` | `ShellSlot`: `floating`, `header` |
| Dashboard widget | `DashboardWidget` (`WidgetSize`: `single`, `double`, `full`) | `DashboardTarget`: `console`, `admin` |
| Settings tab | `SettingsTab` | `SettingsTarget`: `assistant`, `tenant` |
| Table column | `TableColumn` (`CellType`, `Tone` for status cells) | `TableTarget`: `contacts`, `conversations` |

`routes()` registers tenant-wide routes under `apps/<id>/`, `assistantRoutes()` per-assistant ones under
`assistant/{assistant}/apps/<id>/` (the parameter `assistant` is the assistant's key); Core adds the domain, the prefix, the name prefix `solutions.<id>.` and the
`solution` middleware group. Use controllers, not closures, so the routes can be cached. A link action's route
takes a `record` parameter. A table column's values come from a `Console\Contracts\ColumnValuesInterface`
class, called once per table page with the row ids and a `Console\DTO\ColumnContext`.

The DTOs check the format of what they are given and throw `InvalidArgumentException`:

| Name | Format (`ConsoleNames`) | Example |
|------|-------------------------|---------|
| key | `KEY_PATTERN` `^[a-z][a-z0-9_]{0,31}$` | `score` |
| label, menu group | `LABEL_PATTERN`: a translation key in the Solution's namespace | `feedback::console.app` |
| icon | `ICON_PATTERN`: a lucide name in kebab case | `message-circle` |
| route | `ROUTE_PATTERN`: `solutions.<id>.<name>`, non-empty dot-separated segments | `solutions.feedback.index` |
| component | `COMPONENT_PATTERN`: `<id>::<Path>`, PascalCase segments joined by `/` | `feedback::Widgets/Score` |

`ConsoleNames::solutionOf('feedback::Widgets/Score')` returns `feedback`. That the `<id>` in a label, route
or component is the declaring Solution's own is checked by Core.

**`fapost-ui.json`.** The built bundle sits in the package (usually `dist/`), and the manifest points to its
description with `extra.fapost.ui`. Paths are relative to the directory of `fapost-ui.json`.

```json
{
  "schema": 1,
  "solution": "feedback",
  "kit": "^0.1",
  "vue": "^3.5",
  "inertia": "^3.0",
  "pages": { "Reviews/Index": "pages/reviews-index.js" },
  "components": { "Widgets/Score": "components/score.js", "Dialogs/Rate": "components/rate.js" },
  "css": ["assets/feedback.css"]
}
```

| Field | Required | Rule |
|-------|----------|------|
| `schema` | yes | integer; `1` is the only supported number |
| `solution` | yes | the manifest's `id` |
| `kit`, `vue`, `inertia` | yes | non-empty version constraints the bundle was built against; Core checks them against its own |
| `pages`, `components` | no | objects `Path => file`; `Path` is the part of a component name after `<id>::`; files end with `.js`; together at least one entry; a Path is listed once, under `pages` or under `components` |
| `css` | no | list of files ending with `.css` |
| `x-*` | no | ignored |

Files are relative paths of `/`-separated segments made of letters, digits, `.`, `_` and `-` (no empty segment and none starting with `.`, so no `.`, `..` or hidden file). Unknown fields are an error, as in
the manifest. `Console\Bundle\UiBundleParser` is pure like `ManifestParser`: it takes the decoded array and the
Solution id and returns every violation at once; whether the listed files exist is checked by Core.

```php
$result = (new UiBundleParser())->parse(json_decode(file_get_contents('dist/fapost-ui.json'), true), 'feedback');

$result->isValid();   // bool
$result->lines();     // ["feedback: fapost-ui.json: pages.Index: must be a path relative ...", ...]
$result->bundle;      // ?UiBundle
```

`UiBundleSchema::describe()` returns the rules above as data.

### Flow node handlers

`NodeHandlerInterface` is the contract for flow-engine nodes. The engine resolves a handler by
`(type, version)` from an in-memory registry — no DB queries. Versioning rules:

- backward-compatible change (new field with default) → `version()` unchanged;
- breaking change → `version()++`, old handlers stay registered so existing flow definitions
  keep running;
- remove a handler only when no active flow references it.

Handlers must be graph-unaware (return a `sourceHandle`, not the next node id) and safe to retry.
`Flow\Handlers\AbstractVersionedHandler` provides a base for versioned handlers.

## Testing


## Code style

Code is formatted with [Pint](https://laravel.com/docs/pint) using the repository's `pint.json`:

```bash
vendor/bin/pint
```

Every own property and own class constant is typed (`private string $x`, `public const string NAME = '…'`; PHP 8.4).
The one exception is a property or constant that overrides an untyped member of a vendor parent class: it must stay
untyped (PHP would fatal otherwise) and carries a PHPDoc `/** @var … */`.

## License

Licensed under the [Apache License 2.0](https://www.apache.org/licenses/LICENSE-2.0).
