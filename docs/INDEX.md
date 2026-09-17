# telegram-bot-lib — Docs Index

> Package: `bagart/telegram-bot-lib` (BAGArt\TelegramBot). Pure Telegram Bot API library — no Laravel dependency. Verified 2026-09-17 against src/ (772 PHP files).

## Read order

| Need | File |
|---|---|
| What the package is, layout, flows | `SDD-core-lib.md` |
| Active work | `tasks/` (create on demand) |

## Source map (src/)

| Dir | Owns |
|---|---|
| `TgApi/` | ~450 generated DTOs + enums (Methods/, Types/) — regenerate only via `commands/actualize.sh` |
| `TgApiCaller.php`, `TgApiServices/` | API call execution, typed service access |
| `Outbound/` | send pipeline: queue DTOs, middleware, circuit breaker, DLQ, lease |
| `Processing/` | inbound updates: processors, dispatcher, selector |
| `Modules/` | TgModuleContract, descriptor, registrar, registries (command/outbound/web) |
| `Queue/` | queue daemons, job envelopes, adapters |
| `Http/` | Laravel / Pure / Symfony webhook layers |
| `Contracts/` | cross-package interfaces (ApiCommunication, BotServices, Outbound, Queue, Processing) |
| `TgBotSetup.php`, `TgBotSetupFactory.php` | readonly setup DTO + singleton factory (DI root) |
| `Framework/`, `Configs/`, `Exceptions/`, `DevTool/` | framework adapters, config, errors, DTO generator |
