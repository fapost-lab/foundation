# Changelog

Notable changes to `fapost/foundation`. Versions follow [Semantic Versioning](https://semver.org/); while the
major version is `0`, a minor release may break what is marked `@experimental`, and says so here. Releases
before 0.14.0 are described by their tags on GitHub.

## 0.14.0 — unreleased

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
