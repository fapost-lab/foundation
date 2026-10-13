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
| Extension lifecycle | `Contracts`, `Lifecycle`, `Solution\*`, `Support` | Register Solutions/Plugins against the platform; the Solution manifest (`Solution\Manifest\*`: `ManifestParser`, `SolutionManifest`, `ManifestSchema`) and the read-only catalog of installed Solutions (`Solution\Contracts\InstalledSolutionCatalogInterface`, implemented by Core; `Solution\DTO\InstalledSolutionInfo`) |
| Flow engine | `Flow\*`, `Contracts\NodeHandlerInterface`, `Contracts\DataAccessorInterface` | Node handlers, expression engine, triggers, contact writes, scoped state |
| Messaging | `Messaging\*` | Outbound message senders, delivery results, typing/processing indicators |
| Channels | `Channel\*` | Channel adapters and webhook registration |
| Media | `Media\*` | Channel media upload/download contracts and DTOs |
| Tenancy | `Tenancy\*` | Tenant provisioning (`Contracts\TenantProvisionerInterface`), slug reservation and resumable provisioning by tenant id (`Contracts\TenantReservationInterface`), a read-only tenant directory (`Contracts\TenantDirectoryInterface`), changing a tenant's slug and host (`Contracts\TenantRenamerInterface`), support access for platform operators (`Contracts\SupportAccessInterface`) and a tenant's access mode (`Contracts\TenantAccessModeInterface`, implemented by an operator package; Core answers active by default), with their `DTO`, `Enums` and `Exceptions`; implemented by Core |
| Quota | `Quota\*` | Limit registry (`Contracts\LimitRegistryInterface`, implemented by Core and filled by Core and Solutions at boot), per-tenant limits (`Contracts\TenantLimitsInterface`, implemented by an operator package; Core allows everything by default) and the record check Core and Solutions call before creating a counted record (`Contracts\RecordQuotaInterface`, implemented by Core; `Exceptions\RecordLimitReachedException`), the per-period usage meter (`Contracts\UsageMeterInterface`, implemented by an operator package; Core allows everything by default; `DTO\UsageUnit`, `DTO\UsageDecision` with the optional end of the period on a refusal, `Exceptions\VolumeLimitReachedException`), the wording of the notification admins get when a limit refuses work (`Contracts\LimitNoticeInterface`, implemented by an operator package; Core writes its own text by default; `DTO\LimitNotice`), and a read of how much of a Records or Bytes limit a tenant uses now (`Contracts\TenantUsageInterface`, implemented by Core, called by an operator package outside the tenant context), with `DTO\LimitDefinition` and `Enums\LimitKind` |
| RAG | `Contracts\RagAdapterInterface`, `DTO\*Rag*` | Retrieval-augmented generation adapters |
| Analytics | `Analytics\*` | Analytics event DTO, writer contract, event types |
| Inbound/outbound DTOs | `DTO\*` | Incoming/outgoing messages, media, webhook payloads, execution results |

### Extension entry point

An extension never touches `Route`, `Schedule` or `Migrations` directly. It declares intent
through `CoreRegistrarInterface`, whose implementation lives in Core:

```php
protected function registerExtensions(CoreRegistrarInterface $registrar): void
{
    $registrar->registerNodeHandler(SyncEmployeeHandler::class);
    $registrar->registerDataAccessor('hr', HrDataAccessor::class);
}
```

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
| `extra.fapost.id` | yes | `^[a-z][a-z0-9_]{1,31}$`; not `core`, `platform`, `fapost`, `app`, `system`, `flow`, `module`, `rag`. **Never changes**: a new id is a different Solution |
| `extra.fapost.name` | yes | one line, 1 to 60 characters, English |
| `extra.fapost.description` | no | up to 280 characters |
| `extra.fapost.provider` | yes | class name of a provider extending `Lifecycle\AbstractSolutionServiceProvider`; Core boots it |
| `extra.laravel.providers` | forbidden | any entry is a violation (an empty list is fine): Laravel's package discovery would boot a Solution's provider whether or not its manifest holds up |
| `extra.fapost.actions` | no | unique action ids, each starting with `<id>.` |
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
