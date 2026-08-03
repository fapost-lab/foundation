# FAPost Foundation

`fapost/foundation` is the **public contract layer** of the FAPost platform. It holds the
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
FAPost\Foundation\   →  src/
```

## What's inside

| Area | Namespace | Purpose |
|------|-----------|---------|
| Extension lifecycle | `Contracts`, `Lifecycle`, `Manifest`, `Support` | Register and validate Solutions/Plugins against the platform |
| Flow engine | `Flow\*`, `Contracts\NodeHandlerInterface`, `Contracts\DataAccessorInterface` | Node handlers, expression engine, triggers, contact writes, scoped state |
| Messaging | `Messaging\*` | Outbound message senders, delivery results, typing/processing indicators |
| Channels | `Channel\*` | Channel adapters and webhook registration |
| Media | `Media\*` | Channel media upload/download contracts and DTOs |
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

Both declare a `Manifest\SolutionManifest` used for boot-time validation, capability compatibility
checks and degraded-state detection:

```php
public function getManifest(): SolutionManifest
{
    return SolutionManifest::make(
        id: 'hr',
        version: '2.1.0',
        requiresPlatform: '>=1.4.0 <2.0.0',
        requiresCapabilities: ['flow.node_registry', 'flow.data_accessor'],
    );
}
```

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


## License

Licensed under the [Apache License 2.0](https://www.apache.org/licenses/LICENSE-2.0).
