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

php commands/poller-daemon.php                     # receive updates
php commands/poller-daemon.php --help              # show help
php commands/poller-daemon.php                     # receive updates with DTOProcessor
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

php commands/pollers/poller-raw.php --help          # show help
php commands/pollers/poller-raw.php                 # receive updates in raw mode
  --echo                                    # echo reply to messages
  --show                                    # dump update objects
  --token=xxx:xxx                           # use custom token
```

### commands/mapper.php

Simulate incoming webhook payloads.
Offline DTO mapper example

```bash
php commands/mapper.php
```

### commands/webhook.php

You can configure your bot webhook via web
interface: [bagart.github.io/tg-webhook](https://bagart.github.io/tg-webhook).
Is more powerful and useful.

Manage webhook — show current, set or delete.
Secret is auto-generated in format `{botId}:{sha256(tokenPart)}`.

```bash
export TELEGRAM_BOT_TOKEN=xxx:xxx

php commands/webhook-processing.php --help
php commands/webhook-processing.php                                     # show current webhook + auto-secret
php commands/webhook-processing.php --token=xxx:xxx                     # use token. default: export TELEGRAM_BOT_TOKEN=xxx:xxx
php commands/webhook-processing.php --url=https://example.com/tg        # set webhook (secret auto-generated)
php commands/webhook-processing.php --url=... --secret=custom-secret    # set url with custom secret
php commands/webhook-processing.php --url=... --secret                  # set url with empty secret
php commands/webhook-processing.php --delete                            # delete webhook

```

### commands/actualize.sh

Generate Telegram Bot API DTOs.
Actualize is mean: npm update schema + build json + generate DTOs

```bash
./commands/actualize.sh     # actualize
  --full                    # delete DTO and actualize
```

## Webhook Entry Point

Framework-free Example of Webhook entry point for web server with DTOProcessors(default: echo, store)
Usage: point your web server to telegram-bot-lib/public/

Allowed Security:

- IP firewall: allows only Telegram IPs (149.154.160.0/20, 91.108.4.0/22)
- Expect Secret as `{botId}:{sha256(tokenPart)}` format validation via `AutoSecretByTokenService`

## Processors

Update processors handle incoming Telegram updates.
Build-in DTOProcessors

| Processor                  | Description                       |
|----------------------------|-----------------------------------|
| `Processing`        | Build-in Technical                |
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
