# Core Lib — SDD

> `bagart/telegram-bot-lib` — pure Telegram Bot API library (no Laravel in domain code; Laravel adapters isolated in `Framework/`+`Http/Laravel/`). Declarative, code-grounded; see @see tags in DTOs for API links.

## Identity

Single source of Telegram API access + message transport for the platform. Consumed by basic-lib (CLI daemons), management (webhook HTTP), and every feature module.

## Architecture (verified 2026-09-17)

```
Inbound:  webhook → Http/{Laravel,Pure,Symfony} → Processing/ → RegisteredUpdateProcessorSelector → processor
Outbound: domain code → TgSender → Outbound/ queue → middleware chain → TelegramOutboundExecutor → Telegram API
Modules:  Modules/TgModuleContract (descriptor()+register()) → registries (command, outbound middleware, web)
```

- **TgApi/** — generated DTOs/enums only; never hand-edit (`DevTool/DTOGenerator.php`, regenerate via `commands/actualize.sh`).
- **Outbound/** — `OutboundTask/State/Envelope`, `DeadLetterEntry` (readonly Redis state), pipeline middleware: Expiry → RetryBudget → RateLimit → Executor (order at queue level), `OutboundCircuitBreaker` per-bot, `LeaseRenewer`, control-flow exceptions (Retry/Skip/BusinessError). `SKInterruptException` never caught here.
- **Processing/** — update processors, `ProcessorUpdateDaemon`, error handling; commands self-register into `TgCommandRegistry` (flat registry; a matching /command intercepts exclusively).
- **Modules/** — plugin contract consumed by the engine: `descriptor()` pure metadata, `register(TgModuleRegistrar)` idempotent declarations.
- **Queue/** — `ASKQueueDaemon`, job envelopes, Redis adapter glue.
- **DI law** — `TgBotSetupFactory` is internal to `TelegramBotServiceProvider`; Laravel-facing classes take only concrete singletons, never the factory.
- **State law** — Redis holds readonly DTOs + counters only; no behavior objects, no closures; flush on shutdown.

## Key decisions

1. Read-only library: daemons are built by callers (`new TgOutboundDaemon(...)` via `createOutboundDaemonParts()`), never container singletons.
2. Strict contracts: capability discovery via dedicated `*Contract` interfaces (`AtomicDlqQueueContract`, `LeaseRenewableQueueContract`, `OutboundOrderingQueueContract`), no duck-typing.
3. Lazy connections: constructors never open Redis/TCP; `ASKWarmableContract::warm()` primes.
4. Ordering: `orderingKey` (chat_id:session_id) gives per-chat strict order when queue supports `OutboundOrderingQueueContract`.

## Tests

tests/Arch (architecture invariants), Feature, Integration (Pest). Run: host `composer test` chain or module phpunit.
