# telegram-bot-lib

Pure Telegram Bot API library.

## Installation

```bash
composer require bagart/telegram-bot-lib
```

## Commands

### commands/poller.php

Long polling with DTO (typed objects).

```bash
export TELEGRAM_BOT_TOKEN=xxx:xxx           # Default Telegram Token

php commands/tg_daemons-daemon.php                     # receive updates
php commands/tg_daemons-daemon.php --help              # show help
php commands/tg_daemons-daemon.php                     # receive updates with DTOProcessor
  --echo                                    # echo reply to messages
  --store                                   # store messages to database
  --log                                     # log messages to stderr
  --show                                    # dump update objects
  --token=xxx:xxx                           # use custom token
```

### commands/poller-raw.php

Long polling in raw mode: no DTOs, no Processors, no Registry

```bash
export TELEGRAM_BOT_TOKEN=xxx:xxx           # Default Telegram Token

php commands/pollers/tg_daemons-raw.php --help          # show help
php commands/pollers/tg_daemons-raw.php                 # receive updates in raw mode
  --echo                                    # echo reply to messages
  --show                                    # dump update objects
  --token=xxx:xxx                           # use custom token
```

### commands/mapper.php

Simulate incoming webhook payloads. Offline DTO mapper example

```bash
php commands/mapper.php
```

### commands/webhook.php

You can configure your bot webhook via web
interface: [bagart.github.io/tg-webhook](https://bagart.github.io/tg-webhook). Is more powerful and useful.

Manage webhook — show current, set or delete. Secret is auto-generated in format `{botId}:{sha256(tokenPart)}`.

```bash
export TELEGRAM_BOT_TOKEN=xxx:xxx

php commands/tg_webhook-processing.php --help
php commands/tg_webhook-processing.php                                     # show current tg_webhook + auto-secret
php commands/tg_webhook-processing.php --token=xxx:xxx                     # use token. default: export TELEGRAM_BOT_TOKEN=xxx:xxx
php commands/tg_webhook-processing.php --url=https://example.com/tg        # set tg_webhook (secret auto-generated)
php commands/tg_webhook-processing.php --url=... --secret=custom-secret    # set url with custom secret
php commands/tg_webhook-processing.php --url=... --secret                  # set url with empty secret
php commands/tg_webhook-processing.php --delete                            # delete tg_webhook

```

### commands/actualize.sh

Generate Telegram Bot API DTOs. Actualize is mean: npm update schema + build json + generate DTOs

```bash
./commands/tg_actualize.sh     # tg_actualize
  --full                    # delete DTO and tg_actualize
```

## Webhook Entry Point

Framework-free Example of Webhook entry point for web server with DTOProcessors (default: echo, store)
Usage: point your web server to telegram-bot-lib/public/

Allowed Security:

- IP firewall: allows only Telegram IPs (149.154.160.0/20, 91.108.4.0/22)
- Expect Secret as `{botId}:{sha256(tokenPart)}` format validation via `AutoSecretByTokenService`

## Processors

Update processors handle incoming Telegram updates. Build-in DTOProcessors

| Processor                  | Description                       |
|----------------------------|-----------------------------------|
| `Processing`               | Build-in Technical                |
| `MessageEchoProcessor`     | Reply with "echo: {text}"         |
| `MessagePdoStoreProcessor` | Store messages to SQLite database |
| `UpdateLoggerProcessor`    | Log messages to stderr            |

Register processors:

```php
$registry = new TgUpdateProcessorRegistry();
$registry->register(MessageTypeDTO::class, MessageEchoProcessor::class);
$registry->register(MessageTypeDTO::class, UpdateLoggerProcessor::class);

$processor = new TgUpdateProcessor($registry);
$processor->process($update,  $serviceConfig);
```

## Benchmarks

DNS resolution benchmark (`./cmd/xhprof_bench_dns`):

```
./cmd/xhprof_bench_dns
adapter            time↓    rps↑     ok    fail   memΔ score
-----------------------------------------------------------------
ask-dns             0.0648    724.87     47     —     —    98
react-dns           0.0721    651.69     47     —     —    97
native              2.4466     19.17     47     —     —    96
amphp-dns           0.2421    194.11     47     —   10.0M   100
```

Ranked by time (fastest → slowest):

1. **ask-dns** — 0.0648s (724.9 rps, fairness 98/100, mem 10.0 MB)
2. **react-dns** — 0.0721s (651.7 rps, fairness 97/100, mem 10.0 MB)
3. **amphp-dns** — 0.2421s (194.1 rps, fairness 100/100, mem 20.0 MB, 10.0 MB Δ)
4. **native** — 2.4466s (19.2 rps, fairness 96/100, mem 10.0 MB)

## Components

``````
commands - framework-free command for polling and set webhook
public - Framework-free Example of Webhook entry point for web server with DTOProcessors
src/ - framework-free Telegram Bot lib
├── ApiCommunication/  — Guzzler async client and TelegramBot specific options
│   ├── TgBotApiClient — raw Guzzle Api client to Telegram
│   └── TgBotApiDTOClient — full DTO Telegram Client
├── BotServices/
│   ├── AutoSecretByTokenService — secret service as `{botId}:{sha256(tokenPart)}`
│   ├── BotRegistry — Registry of bot to use them by tokens or secrets(webhook)
│   ├── BotSecretDTO — Bot token and secret DTO
│   └── WebhookManager — get/set/delete webhook, auto-secret, buildTextInfo
├── Contracts/ - All copde with strict consistency contracts 
├── DevTool/
│   └── DTOGenerator — DTO AutoGenerator for Telegram Types and Methods
├── ExampleServices/ — framework-free services 
│   ├── TgPureFactory — factory for example commands and webhook
│   └── TinyFileCache — simple framework-free PSR-16 file cache implementation
├── Http/
│   ├── Validators/ — simple framework-free PSR-16 file cache implementation
│   │   ├── Laravel/ — Laravel middleware
│   │   │   └── ..
│   │   ├── Symfony/ — Symfony middleware
│   │   │   └── ..
│   │   ├── TelegramIpValidator — Validate Telegram Bot Webhook by IP ranges
│   │   └── AutoSecretByTokenService  — Validate Telegram Bot Webhook by secret(auto secret by default)
│   ├── TgApiResponse — Regular Telegram Response format
│   ├── TgApiWebhookHandler — framework-free Response Processor for controller
│   └── TgBotApiReturnParser — ^^^^^^^ is need to union
├── TgApi/  — Autogenerated DTO and Enum. Try to not edit. It will auto-re-generated
│   ├── Methods/  — Autogenerated DTO and Enum for Methods
│   ├── Types/  — Autogenerated DTO and Enum for Types
│   └── TgApiEntityScopeEnum — Discovery of TelegramScope (Methods|Types) with all AutoGenerated DTO List
├── TgApiServices/ — Services for DTO
│   ├── TgApiDTOMapper — Map Raw(array) Telegram Types/Methods to DTO
│   ├── TgApiProperty  — data schema for TgApiDTOContract::tgPropertyMetas 
│   ├── TgEntityNamer — Simple helper to return nname of user|chat|etc
│   ├── TgEntityToDTORegistry — Registry of TgEntity(original terlegram name) to DTO. Can be registered Outaside of TelegramBot Lib
│   └── TgEntityToDTORegistryFactory — Factory of TgEntityToDTORegistry
├── TypeDTOProcessor/ — Processors with Resistry to process incoming returns with TypeDTOContracts
│   ├── Processors/ — Default Processods
│   │   └── ..
│   └── TypeDTOProcessorRegistry  — Registry for Processors By TgApiTypeDTO
├── Wrappers/ — Wrappers for Framework-free code
│   ├── TgBotCacheWrapper  — Cache Wrapper
│   └── TgBotLogWrapper — Logger Wrapper
└── TelegramBotServiceProvider — Laravel ServiceProvider (optional)
