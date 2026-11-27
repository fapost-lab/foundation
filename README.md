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
| Extension lifecycle | `Contracts`, `Lifecycle`, `Solution\*`, `Support` | Register Solutions/Plugins against the platform; the Solution manifest (`Solution\Manifest\*`: `ManifestParser`, `SolutionManifest`, `ManifestSchema`) and the read-only catalog of installed Solutions (`Solution\Contracts\InstalledSolutionCatalogInterface`, implemented by Core; `Solution\DTO\InstalledSolutionInfo`), and which installed Solutions a tenant may run (`Solution\Contracts\SolutionEntitlementInterface`, implemented by an operator package; Core entitles every Solution by default; `Solution\DTO\SolutionEntitlement`, which grants a Solution with `Solution\DTO\SolutionGrant` or only allows it), and the Solutions an operator offers a tenant to connect (`Solution\Contracts\SolutionStorefrontInterface`, experimental; Core offers none by default) |
| Console UI (experimental) | `Console\*` | What a Solution adds to the console: its own application, context actions, shell elements, dashboard widgets, settings tabs and table columns (`Console\Contracts\ConsoleExtensionsInterface`, implemented by Core; `Console\Contracts\ColumnValuesInterface`, implemented by a Solution), with their `DTO`, `Enums`, name formats (`ConsoleNames`) and the `fapost-ui.json` reader (`Console\Bundle\*`) |
| Flow engine | `Flow\*`, `Contracts\NodeHandlerInterface`, `Contracts\DataAccessorInterface` | Node handlers, expression engine, triggers, contact writes, scoped state |
| Messaging | `Messaging\*` | Outbound message senders, delivery results, typing/processing indicators; the `OutboundMessage` envelope carries no secret, and a provider sender gets the channel (`Channel\ChannelInterface`) when it delivers |
| Channels | `Channel\*` | Channel adapters and webhook registration |
| Media | `Media\*` | Channel media upload/download contracts and DTOs |
| Tenancy | `Tenancy\*` | Tenant provisioning (`Contracts\TenantProvisionerInterface`), slug reservation and resumable provisioning by tenant id (`Contracts\TenantReservationInterface`), a read-only tenant directory (`Contracts\TenantDirectoryInterface`), changing a tenant's slug and host (`Contracts\TenantRenamerInterface`), support access for platform operators (`Contracts\SupportAccessInterface`), a tenant's access mode (`Contracts\TenantAccessModeInterface`, implemented by an operator package; Core answers active by default), removing a tenant completely (`Contracts\TenantDecommissionerInterface`, implemented by Core, with the `Events\TenantDecommissioned` it dispatches) and whether a tenant's owner may delete the workspace (`Contracts\TenantSelfDeletionPolicyInterface`, implemented by an operator package; Core answers false by default), with their `DTO`, `Enums` and `Exceptions`; implemented by Core |
| Platform settings (experimental) | `Settings\*` | Core's platform-wide settings, shown and changed by an operator package (`Contracts\PlatformSettingsInterface`, implemented by Core), with the field descriptions it renders from (`DTO\PlatformSettingDefinition`, `Enums\PlatformSettingType`), the values in effect (`DTO\PlatformSettingValue`) and the refusal of a change (`Exceptions\InvalidPlatformSettingsException`) |
| Credentials (experimental) | `Credentials\*` | A tenant's saved credentials, read-only (`Contracts\CredentialStoreInterface`, implemented by Core): references without values for a picker, and one credential with its decrypted values for one call (`DTO\CredentialSecret`, which refuses serialization and masks its values in dumps); credential types and their fields (`Contracts\CredentialTypeInterface`, `DTO\CredentialField`, `Enums\CredentialFieldKind`), and how an HTTP type authorizes a request (`Contracts\HttpCredentialTypeInterface`, `DTO\HttpAuthorization`), with their `Exceptions`; a flow `call` carries the chosen credential in `Flow\Call\CallRequest::$credential` |
| One-time codes (experimental) | `Auth\*` | Delivering the one-time codes a flow's `auth_request` node sends to a contact (`Contracts\OneTimeCodeSenderInterface`, implemented by Core for email and by packages for SMS or their own mail; `Enums\OneTimeCodeChannel`, `DTO\OneTimeCodeMessage`, which refuses serialization and masks its code in dumps, `DTO\OneTimeCodeDelivery`), and the registry a package adds its sender to at boot (`Contracts\OneTimeCodeSenderRegistryInterface`, implemented by Core) |
| Quota | `Quota\*` | Limit registry (`Contracts\LimitRegistryInterface`, implemented by Core and filled by Core and Solutions at boot), per-tenant limits (`Contracts\TenantLimitsInterface`, implemented by an operator package; Core allows everything by default) and the record check Core and Solutions call before creating a counted record (`Contracts\RecordQuotaInterface`, implemented by Core; `Exceptions\RecordLimitReachedException`), the per-period usage meter (`Contracts\UsageMeterInterface`, implemented by an operator package; Core allows everything by default; `DTO\UsageUnit`, `DTO\UsageDecision` with the optional end of the period on a refusal or a warning, and `UsageDecision::approaching()` for an allowed unit that asks Core to warn the tenant's admins that the limit is close, `Exceptions\VolumeLimitReachedException`), the wording of the notification admins get when a limit refuses work, is filled or is close to being used up (`Contracts\LimitNoticeInterface`, implemented by an operator package; Core writes its own text by default; `DTO\LimitNotice`), and a read of how much of a Records or Bytes limit a tenant uses now (`Contracts\TenantUsageInterface`, implemented by Core, called by an operator package outside the tenant context), and the operator's read of how much of each per-period limit a tenant has used in its current period, with an optional upgrade button, shown on the tenant admin's usage page (`Contracts\PeriodUsageInterface`, implemented by an operator package; Core reports nothing by default; `DTO\PeriodUsage`, `DTO\PeriodUsageReport`), with `DTO\LimitDefinition` and `Enums\LimitKind` |
| RAG | `Contracts\RagAdapterInterface`, `DTO\*Rag*` | Retrieval-augmented generation adapters |
| Analytics | `Analytics\*` | Analytics event DTO, writer contract, event types; which flow builder mode a tenant's staff use (`Contracts\BuilderModeUsageInterface`, implemented by Core, called by an operator package outside the tenant context; `DTO\BuilderModeUsage`) and the event Core dispatches when a staff user switches it (`Events\BuilderModeChanged`), both experimental |
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
Solution registers against the Solution's manifest (`extra.fapost.actions`, `extra.fapost.nodes`, `extra.fapost.triggers`, `extra.fapost.command_types`, `extra.fapost.commands`). A
Solution registers **action handlers** (`registerActionHandler`, by class), since foundation 0.36,
**node handlers** (`registerNodeHandler`) for the node types its manifest declares, and, since foundation 0.46,
**trigger types** (`registerTriggerType`, experimental) for the trigger types its manifest declares, and, since foundation 0.50,
**command action types** and **chat commands** (`registerCommandActionType`, `registerCommand`, experimental). The provider's `boot()` only runs `bootSolution()`.

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
| `extra.fapost.id` | yes | `^[a-z][a-z0-9_]{1,31}$`; not `core`, `platform`, `fapost`, `app`, `system`, `flow`, `module`, `rag`, `solution`, `solutions`, `console`, `admin`, `contact`, `call`. **Never changes**: a new id is a different Solution |
| `extra.fapost.name` | yes | one line, 1 to 60 characters, English |
| `extra.fapost.description` | no | up to 280 characters |
| `extra.fapost.provider` | yes | class name of a provider extending `Lifecycle\AbstractSolutionServiceProvider`; Core boots it |
| `extra.laravel.providers` | forbidden | any entry is a violation (an empty list is fine): Laravel's package discovery would boot a Solution's provider whether or not its manifest holds up |
| `extra.fapost.actions` | no | unique action ids, each starting with `<id>.` |
| `extra.fapost.nodes` | no | unique flow node types, each `<id>.` followed by a snake_case name; since foundation 0.36 |
| `extra.fapost.triggers` | no | unique flow trigger types, each `<id>.` followed by a snake_case name, at most 64 characters in all; since foundation 0.46, experimental (see [Flow trigger types](#flow-trigger-types-experimental)) |
| `extra.fapost.command_types` | no | unique command action types, each `<id>.` followed by a snake_case name, at most 64 characters in all; since foundation 0.50, experimental (see [Chat commands](#chat-commands-experimental)) |
| `extra.fapost.commands` | no | unique chat commands, each `/` followed by 1 to 32 lowercase letters, digits or underscores, none of `/reset`, `/cancel`, `/start`, `/stop`; since foundation 0.50, experimental |
| `extra.fapost.state` | no | boolean, default `false`; `true` = the Solution owns the flow state namespace named by its id, writable only by its own node types; since foundation 0.36 |
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

### Chat commands (experimental)

A global chat command (such as `/reset`) is matched before a message reaches a flow. A Solution adds two things, since
foundation 0.50:

- a **command action type**, `Flow\Commands\CommandActionTypeInterface`, next to Core's `terminate_session`,
  `start_flow` and `send_message`: its `type()` (declared in `extra.fapost.command_types`), a `label()`, a
  `configSchema()` and `validateConfig()` for the config a tenant gives one command of the type, and
  `handle(CommandContext $context, array $config): CommandOutcome`. Registered with
  `$registrar->registerCommandActionType(ThankCommandType::class)`;
- a ready-made **chat command**, `Flow\Commands\SolutionCommandInterface`: its `command()` (declared in
  `extra.fapost.commands`), the `actionType()` it takes (a type of the same Solution, or Core's `send_message` or
  `terminate_session`), its `config()` and a `label()`. Registered with `$registrar->registerCommand(FeedbackCommand::class)`.

Both work only for a tenant that activated the Solution. A command runs before Core takes the session's lock, so
`handle()` never touches the session: it returns `CommandOutcome::reply()`, `none()`, `endSession()` or `startFlow()`,
and Core carries it out. Commands are matched in this order: the tenant's own commands of the assistant, a Solution's,
Core's built-in ones; two installed Solutions that claim one command are both refused by Core.

### Flow trigger types (experimental)

A Solution adds a kind of flow trigger next to Core's built-in ones with `Flow\Contracts\TriggerTypeInterface`:
its `type()` (declared in `extra.fapost.triggers`), a `label()`, a `configSchema()` the builder renders as the
trigger's form, `validateConfig()` (checked at publish; an error blocks it), `matches($config, $payload)` (whether
one fire starts the trigger configured so) and `payloadFields()` (shown with the trigger as `flow.trigger.<key>`). Every
method is pure. The provider registers it with `$registrar->registerTriggerType(SubmittedTrigger::class)`.

The Solution's own code starts runs with `Flow\Contracts\TriggerFirerInterface::fire($tenantId, $type, $fireKey,
$payload)`, taken as a constructor dependency. Core queues the fan-out after the caller's transaction commits and
starts one contactless run per active trigger of the type whose config matches, once per fire key (while Core keeps
the run), while the Solution is active for the tenant. Only a fire from inside a run counts towards Core's chain limit
of five; a loop through the extension's own queued job is the extension's to break. The payload (JSON, at most `TriggerFirerInterface::MAX_PAYLOAD_BYTES`) is the
run's `flow.trigger.*`.

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
