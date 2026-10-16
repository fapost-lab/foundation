# Changelog

Notable changes to `fapost/foundation`. Versions follow [Semantic Versioning](https://semver.org/); while the
major version is `0`, a minor release may break what is marked `@experimental`, and says so here. Releases
before 0.14.0 are described by their tags on GitHub.

## Unreleased

### Added

- `Channel\ChannelInboundAdapterInterface`, experimental: the inbound half of a connector's channel
  (`normalize(array $payload): IncomingMessage`, plus `ingressSpec()`). Verification and the idempotency key stay
  declarative; a connector ships no verification code.
- `Connector\ConnectorChannel`, experimental: the classes that serve one channel type (sender, inbound adapter and
  optional webhook registrar and media services), as class names.
- `Connector\ConnectorRegistrarInterface`, experimental: `registerChannel()`, given by Core to one connector.
- `Lifecycle\AbstractConnectorServiceProvider::registerChannelsWith()`: Core's entry point for channel registration.

### Changed

- Experimental, breaking: `AbstractConnectorServiceProvider` now requires `registerChannels()`.

## 0.22.0

### Added

- `Media\ChannelMediaLinkPolicyInterface`: a channel type declares that it sends media by a public URL instead of
  uploading bytes (`channelType()`, `linkTtlSeconds(): ?int`). Core then hands it, in `MessagePayload::$media`, a
  signed link to the media file that expires, issued just before delivery, with `mime_type`, `size`, `file_name` and
  `kind`; the channel never issues links itself. Core caps the requested lifetime. A channel type with an uploader
  keeps uploading, so nothing changes for existing channels.

## 0.21.0

### Added

- Connector manifest, experimental: a connector is a deploy-time Composer package of type `fapost-connector`
  (`Solution\Manifest\ManifestSchema::CONNECTOR_PACKAGE_TYPE`) that brings a messaging channel. Its identity is the
  `extra.fapost` block of its `composer.json` (schema 1: `schema`, `id`, `name`, `description`, `provider`, `channels`),
  read without running its code by `Connector\Manifest\ConnectorManifestParser` into a
  `Connector\Manifest\ConnectorManifest`; `ConnectorManifestSchema` describes the format. The id, name and
  description rules, the `require` rules and the ban on Laravel package discovery are the Solution ones.
- `Lifecycle\AbstractConnectorServiceProvider`: base provider for a connector (`registerBindings()`, `bootConnector()`).
  Core boots a connector for the whole installation, with no per-tenant activation.

## 0.20.1

### Fixed

- `Channel\Ingress\IngressSpecExecutor::verify()` refuses every scheme but `none` when the channel secret is empty.
  Before, an empty secret matched a request with no `header_equals` header or `query_param` parameter, and as an
  HMAC key it let anyone sign a body. Every ingress runtime must do the same; Core's golden fixtures hold it.

## 0.20.0

### Added

- `Solution\Contracts\SolutionEntitlementInterface::entitlementFor(string $tenantId): SolutionEntitlement`: an operator
  package answers which installed Solutions a tenant may run (for example its plan's bundle and its active add-ons).
  Core runs a Solution only when the tenant turned it on, it is installed and it is entitled; a Solution the answer
  stops listing stops running with its activation and data kept, and runs again when the answer lists it again.
  Core refuses to turn on a Solution that is not entitled, asks at most once per tenant per request or job, and
  treats an exception as "keep what runs, refuse to turn anything on". Core binds a default that entitles every
  Solution, so nothing changes until an operator package implements it.
- `Solution\DTO\SolutionEntitlement`: `all()` (nothing restricted) or `only(list $solutionIds, ?string $actionLabel,
  ?string $actionUrl)` with an optional button for the tenant's admins; `allows()` and `isUnrestricted()`. `only()`
  drops duplicate ids and throws `InvalidArgumentException` on an empty id, an action with only a label or only a
  URL, or a URL that is not absolute http(s). Ids this image does not install are allowed.

## 0.19.0

### Added

- `Quota\Contracts\PeriodUsageInterface::reportFor(string $tenantId, string $locale): ?PeriodUsageReport`: an operator
  package reports how much of each per-period limit a tenant has used in its current period (used, limit, start and
  end of the period), and optionally one action button such as "Upgrade plan". Core shows it on the tenant admin's
  usage page and calls it at most once per page view, treating an exception as "not reported". Core binds a default
  that returns `null`, so nothing changes until an operator package implements it.
- `Quota\DTO\PeriodUsage` and `Quota\DTO\PeriodUsageReport`: the report's entries and envelope. Both validate in the
  constructor (non-negative numbers, a period that ends after it starts, no duplicate key, an action with a label and
  an absolute http(s) URL together) and throw `InvalidArgumentException`.

## 0.18.0

### Added

- `Quota\DTO\UsageDecision::approaching(int $limit, int $used, ?DateTimeImmutable $periodEndsAt = null)` and the
  `UsageDecision::$approaching` flag: an operator's usage meter returns it for an allowed unit that brings the
  period's use to its warning threshold, and Core warns the tenant's admins that the limit is close. Flag once per
  tenant, key and period; Core also sends at most one warning per limit and period. Nothing changes until a meter
  flags a decision.
- `UsageDecision::allowed()` takes an optional third argument `?DateTimeImmutable $periodEndsAt`; Core reads it from a
  warning and from a refusal, and ignores it on a plain allowed decision.
- `Quota\Enums\LimitNoticeReason::Approaching`: the reason Core passes to `LimitNoticeInterface::noticeFor()` when it
  asks for the wording of such a warning. An implementation of `LimitNoticeInterface` whose `match` has no default arm
  throws `UnhandledMatchError` on `Approaching`; Core catches the error, reports it and falls back to its own text.

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
