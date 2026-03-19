<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\Outbound;

use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;

/**
 * Unified Telegram API message sending client.
 *
 * Injected into processors. Thin layer: accepts DTO and pushes task
 * to {@see OutboundQueueContract}. All delivery logic, retry, rate limiting,
 * error classification — in Worker + Pipeline (see todo.md §7.1, §5.1).
 *
 * All Telegram API sends go ONLY through this contract (todo_task.md §6 invariants,
 * §12 anti-patterns — no parallel send paths).
 */
interface TgSenderContract
{
    /**
     * Asynchronously send DTO to Telegram via outbound queue.
     *
     * Does not block the caller — method returns immediately after push to queue.
     *
     * @param  TgBotConfig  $botConfig  Bot config (contains botId, token resolved by Executor).
     * @param  TgApiMethodDTOContract  $dto  Telegram API method (sendMessage, sendPhoto …).
     */
    public function send(TgBotConfig $botConfig, TgApiMethodDTOContract $dto): void;
}
