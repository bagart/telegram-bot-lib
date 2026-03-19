# telegram-bot-lib Architecture Context

You are a senior PHP architect. We are developing the **telegram-bot-lib** library — the core of Telegram Bot Platform.

Communication with the LLM-developer can be in Russian.
All code comments and text must be in English.

---

## Library's place in the ecosystem

```
application
    │
    ▼
telegram-bot-management-lib  (Laravel models, commands, Laravel Queue adapters)
    │
    ▼
telegram-bot-lib             (Telegram API, outbound pipeline, processors)
    │
    ▼
telegram-bot-basic-lib       (webhook handlers, middleware, basic commands)
    │
    ▼
php-async-kernel-client      (ASKClient — universal execution engine)
    │
    ▼
php-async-kernel             (scheduler, cache, queue, network primitives)
```

`telegram-bot-lib` contains:
- Telegram API DTOs (generated, `src/TgApi/`)
- Api transports, clients, rate limiters (`src/ApiCommunication/`)
- **Outbound Pipeline** (`src/Outbound/`) — unified outbound sending pipeline
- Processors (`src/Processing/`) — incoming update handlers
- TgBotSetup / TgBotSetupFactory — DI wiring
- TelegramBotServiceProvider — Laravel bindings

---

## Outbound Pipeline Architecture

### Goal

A unified async pipeline for sending responses to Telegram API.

Processor → `TgSender` → `OutboundQueueContract` → `TgOutboundDaemon` → `OutboundPipeline` → `TelegramOutboundExecutor`.

### Principles

1. **TgSender** — constant name, injected into processors. Only pushes to queue.
2. **Middleware contract** (PSR-15 style): `OutboundMiddleware::handle($task, $next)`.
3. **Pipeline order**: RetryPolicy → RateLimit → Ordering → Executor.
4. **Task immutable**, State mutable → **Envelope** (Task + State) on serialization.
5. **RetryDecision** — value object (delay, reason), not exception for control flow.
6. **ErrorClassifier** — separate class, isolated from Executor.
7. **Visibility lease** — pop with 60s lease, crash recovery via expired lease.
8. **All communication through contracts**: `OutboundQueueContract`, `CacheContract`, `RateLimiterContract`.
9. **Framework-independent** — daemon (standalone) and artisan command, same logic.

### Error Model

Telegram always returns HTTP 200. Errors are in JSON `error_code`.

| Code | Exception | Retryable? | Action |
|------|-----------|-----------|--------|
| 400 | `TgBadRequestException` | No | business_error → DLQ |
| 401, 403, 404 | `TgApiException` | No | business_error → DLQ |
| 409 | `TgApiConflictException` | Yes | retry |
| 429 | `TgApiRateLimitException` | Yes | retry (with retry_after) |
| 500-503 | `TgApiException` | Yes | retry |
| Network | `TgApiNetworkException` | Yes | retry |
| "Try later" > 1h | — | No | business_error → DLQ |

### Key Contracts

```
TgSenderContract::send(TgBotConfig, TgApiMethodDTOContract): void

OutboundQueueContract::push(queue, payload)
                     ::pushDelayed(queue, payload, delayAt)
                     ::pop(queue, visibilityTimeout): ?[id, payload]
                     ::acknowledge(queue, id)
                     ::release(queue, id)
                     ::size(queue): int

OutboundMiddleware::handle(OutboundEnvelope, Closure $next): void

CacheContract::incrementWithTtl(key, value, ttl): int
             ::lock(key, ttl, ?owner): bool
             ::unlock(key, ?owner): void

RateLimiterContract::acquire(key): ?int   // null = ok, int = delay
                   ::registerRetryAfter(key, seconds): void
```

### Envelope: Task + State

Envelope is serialized to the queue so that pushDelayed preserves attempt and errorContext:

```
OutboundEnvelope {
    task: OutboundTask     // immutable: id, botId, dtoClass, dto, createdAt
    state: OutboundTaskState  // mutable: status, attempt, errorContext
}
```

Statuses: pending → in_progress → delivered / business_error.

### RateLimitMiddleware — Lock Safety

Lock on chatId for ordering. **Guaranteed release** in `finally`:

```
try {
    $next($envelope);
} finally {
    if ($lockKey !== null) {
        $cache->unlock($lockKey, owner: $envelope->task->id);
    }
}
```

### Data Flow

**Producer (sending):**
```
Processor::handle($update)
  → $sender->send($botConfig, $dto)
    → new OutboundEnvelope(Task, State)
    → $queue->push('tg-outbound', json_encode($envelope))
  ← void
```

**Worker (processing):**
```
OutboundWorker::tick()
  → queue->pop('tg-outbound', visibility: 60) → $envelope
  → $state->attempt++
  → try {
      OutboundPipeline::execute($envelope)
      $state->status = 'delivered'
      queue->acknowledge()
    } catch (RetryDecision $d) {
      $state->status = 'pending'
      queue->pushDelayed('tg-outbound', json_encode($envelope), time() + delay)
      queue->acknowledge()
      stats->recordRetry()
    } catch (\Throwable $e) {
      $state->status = 'business_error'
      $dlqEntry = DeadLetterEntry::fromEnvelope($envelope, $e->getMessage())
      queue->push('tg-dlq', json_encode($dlqEntry))
      queue->acknowledge()
      stats->recordBusinessError()
    }
```

**Pipeline (execution):**
```
RetryPolicyMiddleware (expiry, max attempts)
  → RateLimitMiddleware (rate limit + ordering lock)
  → TelegramOutboundExecutor (dtoClient->request() + classifier)
```

### Priority-Based Retry

Delay is calculated at failure time, not in tick():

```
attempt 1-2 → 1s + jitter
attempt 3   → 5s + jitter
attempt 4   → 15s + jitter
attempt 5   → 30s + jitter
default     → 60s + jitter
```

Jitter: `$base + random_int(0, (int)($base * 0.1))`

### Expiry

- > 1 hour at >= 2 attempts → business_error → DLQ
- `RetryPolicyMiddleware` throws `OutboundSkipException`

### Dead Letter Queue

DLQ entries are structured: taskId, reason, firstFailedAt, attempts, lastError, payloadHash.

**DLQ retry** — `TgOutboundDlqCommand --retry {id}` / `--retry-all`:
- Creates a new `OutboundEnvelope` with `attempt = 0` (reset)
- Pushes to `tg-outbound` via `OutboundQueueContract::push()`
- Goes through full pipeline (including rate limit)
- DLQ entry is deleted or marked `recovered`

DLQ is storage only. Execution always goes through the main pipeline.

### Metrics

`TgOutboundStats` — single class:
- `recordSent/Retry/Failed/BusinessError` — incremental
- `getGlobalMetrics/getBotMetrics` — read aggregations
- Atomic INCR + TTL (Lua for Redis, mutex for memory)
- Keys: `tg_global_metrics:{hour}:{metric}`, `tg_bot_metrics:{botId}:{hour}:{metric}`

### Memory Management (Daemon)

- `gc_collect_cycles()` when iteration threshold is exceeded in tick()
- Graceful shutdown — Worker completes current task

### File Structure

```
src/Outbound/
├── TgSender.php
├── OutboundTask.php
├── OutboundTaskState.php
├── OutboundEnvelope.php
├── OutboundPipeline.php
├── OutboundMiddleware.php              (interface)
├── RetryPolicyMiddleware.php
├── RateLimitMiddleware.php
├── TelegramOutboundExecutor.php
├── OutboundErrorClassifier.php
├── OutboundDecision.php
├── OutboundWorker.php
├── OutboundSkipException.php
├── TgOutboundStats.php
├── DeadLetterEntry.php
├── Contracts/
│   ├── OutboundQueueContract.php
│   ├── RateLimiterContract.php
│   └── CacheContract.php
└── Adapters/
    └── LaravelQueueAdapter.php

commands/
└── outbound-metrics-daemon.php
```

### What should NOT be in telegram-bot-lib

- Laravel-specific code (commands, models, migrations) → in telegram-bot-management-lib
- Pure HTTP webhook handler → in telegram-bot-basic-lib
- Async execution primitives (scheduler, promises) → in php-async-kernel / php-async-kernel-client
- Redis queue implementation → in php-async-kernel-client-redis

telegram-bot-lib contains only Telegram-specific logic and framework-independent contracts.
