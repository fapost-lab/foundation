# Changelog

Notable changes to `fapost/foundation`. Versions follow [Semantic Versioning](https://semver.org/); while the
major version is `0`, a minor release may break what is marked `@experimental`, and says so here. Releases
before 0.14.0 are described by their tags on GitHub.

## Unreleased

### Changed

- Docs: per-assistant Solution routes are `assistant/{assistant}/apps/<id>/`. Core renamed the route parameter from
  `tenant` to `assistant` (Core's console route names moved from `filament.*` to `console.*` at the same time);
  the URIs did not change. A Solution that builds those URLs with `['tenant' => …]` must pass `['assistant' => …]`.

## 0.17.0

### Added

- `Contracts\CoreRegistrarInterface::registerActionHandler(class-string<ActionHandlerInterface>)`: a Solution
  registers an action handler by class. Core builds the handler on each call and reads `id()` from one throwaway
  instance at registration.
- `Lifecycle\AbstractSolutionServiceProvider::registerExtensionsWith(CoreRegistrarInterface $registrar)` (final):
  the entry point Core calls, once per process and with a registrar of its own per Solution.

### Changed

- **BREAKING:** `AbstractSolutionServiceProvider::boot()` no longer pulls a `CoreRegistrarInterface` from the
  container and no longer calls `registerExtensions()`; it only runs `bootSolution()`. Core calls
  `registerExtensionsWith()` before any `boot()`. A Solution that relied on `boot()` to register extensions needs
  nothing but to run under a Core that implements this.

## 0.16.0

### Changed

- Solution manifest: `console` and `admin` are reserved ids (`ManifestSchema::RESERVED_IDS`). They are the keys of
  Core's own applications in the console rail, and Core already refuses a Solution application under either key.
  This narrows what the manifest accepts, so it ships in a minor release: a Solution with either id is refused.

## 0.15.0

### Added

- `Console\*` (experimental): the contract through which a Solution adds UI to the console.
  - `Console\Contracts\ConsoleExtensionsInterface`, implemented by Core: `application()`, `contextAction()`,
    `shellElement()`, `dashboardWidget()`, `settingsTab()`, `tableColumn()`, `routes()`, `assistantRoutes()`.
  - `Console\Contracts\ColumnValuesInterface`, implemented by a Solution, with `Console\DTO\ColumnContext`.
  - DTOs `ConsoleApplication`, `MenuItem`, `ContextAction` (`link()`, `dialog()`), `ShellElement`,
    `DashboardWidget`, `SettingsTab`, `TableColumn`; each checks the format of its names and throws
    `InvalidArgumentException`.
  - Enums `ConsoleTarget`, `ShellSlot`, `DashboardTarget`, `SettingsTarget`, `TableTarget`, `ContextActionKind`,
    `WidgetSize`, `CellType`, `Tone`.
  - `Console\ConsoleNames` (name formats, `solutionOf()`) and
    `Console\Exceptions\InvalidConsoleDeclarationException`.
  - `Console\Bundle\UiBundleParser`, `UiBundleSchema`, `UiBundle`, `UiBundleParseResult`, `UiBundleViolation`:
    reading a Solution's `fapost-ui.json`, schema 1.
- Solution manifest: optional `extra.fapost.ui`, the path of the Solution's `fapost-ui.json` inside the package
  (experimental). `SolutionManifest` takes it as a new last constructor parameter `?string $ui = null`, so
  existing calls keep working.

## 0.14.0

### Changed

- Solution manifest: `solution` and `solutions` are reserved ids (`ManifestSchema::RESERVED_IDS`).
