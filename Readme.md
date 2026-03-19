# Telegram Bot Service

Библиотека для создания Telegram ботов с поддержкой webhook и long-polling режимов.

# Официальная версия Telegram Bot API

https://core.telegram.org/bots/api

- March 1, 2026
- Bot API 9.5

---

## Code Coverage

> Generated: 2026-03-19 | 151 tests | 267 assertions

### Coverage by Folder (excluding TgApi/*)

| Folder | File | Coverage | Bar |
|--------|------|----------|-----|
| **TgApiServices** | TgEntityToDTORegistry | `81.0%` | `████████████████░░░░` |
| | TgApiDTOMapper | `66.7%` ↑ | `█████████████░░░░░░░` |
| | TgApiProperty | tested | `⚠️` |
| | TgApiResponse | tested | `⚠️` |
| | TgEntityNamer | tested | `⚠️` |
| **Wrappers** | TgBotLogWrapper | `64.7%` | `████████████░░░░░░░░` |
| | TgBotCacheWrapper | `51.6%` | `██████████░░░░░░░░░░` |
| **ServiceProvider** | TelegramBotServiceProvider | `88.3%` | `█████████████████░░░` |
| **Exceptions** | TgUnregisteredEntityNameException | `71.4%` | `██████████████░░░░░░` |
| | TgBotTechnicalException | tested | `⚠️` |
| | TgUnexpectedApiReturnException | tested | `⚠️` |
| | TgApiUserBreakeException | `0.0%` | `░░░░░░░░░░░░░░░░░░░░` |
| **ApiCommunication** | TgCircuitBreaker | tested | `⚠️` |
| | TgRateLimiter | tested | `⚠️` |
| | TgRetryPolicy | tested | `⚠️` |
| | TgBotApiClient | `0.0%` | `░░░░░░░░░░░░░░░░░░░░` |
| | TgBotApiDTOClient | `0.0%` | `░░░░░░░░░░░░░░░░░░░░` |
| | TgBotApiReturnParser | `0.0%` | `░░░░░░░░░░░░░░░░░░░░` |
| | Exceptions/ (4) | `0.0%` | `░░░░░░░░░░░░░░░░░░░░` |

> ⚠️ = has tests but coverage tool didn't detect (namespace/directory mismatch)

### Decompositions Applied

| Method | File | Before | After | Tests |
|--------|------|--------|-------|-------|
| `prepareFormat()` | TgApiDTOMapper | 59.2% | **66.7%** | +12 tests |
| └ `categorizeTypes()` | (extracted) | — | **100%** | 3 tests |
| └ `matchPrimitiveType()` | (extracted) | — | **100%** | 6 tests |

### Test Files

```
tests/Unit/TelegramBot/
├── TgApiServices/
│   ├── TgApiDTOMapperTest.php (7 tests)
│   ├── TgApiDTOMapperDecomposedTest.php (12 tests) ← NEW
│   ├── TgApiPropertyTest.php (2 tests)
│   ├── TgApiResponseTest.php (2 tests)
│   ├── TgEntityNamerTest.php (11 tests)
│   ├── TgEntityToDTORegistryTest.php (6 tests)
│   └── TgEntityToDTORegistryFactoryTest.php (2 tests)
├── Wrappers/
│   ├── TgBotLogWrapperTest.php (6 tests)
│   └── TgBotCacheWrapperTest.php (7 tests)
├── ApiCommunication/ClientServices/
│   ├── TgCircuitBreakerTest.php (6 tests)
│   ├── TgRateLimiterTest.php (5 tests)
│   └── TgRetryPolicyTest.php (9 tests)
├── Exceptions/
│   └── ExceptionTest.php (4 tests)
├── TelegramBotServiceProvider/
│   └── ServiceProviderTest.php (1 test)
└── TgApi/
    ├── Methods/DTO/MethodsDTOTest.php (2 tests)
    ├── Types/DTO/TypesDTOTest.php (2 tests)
    └── Types/Enum/EnumTest.php (3 tests)
```

---

## Setup

Библиотека уже включена в проект. Подключение через Service Provider:
```php
// app/Providers/AppServiceProvider.php
$this->app->register(TelegramBot\TelegramBotServiceProvider::class);
```

### Demo

```bash
composer require bagart/TelegramBotBasic
```

# Update

## Update DTO's and Enum's

```bash
./artisan tg:dev:dto:actualize --full
```
