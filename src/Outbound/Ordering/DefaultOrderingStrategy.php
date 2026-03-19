<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Outbound\Ordering;

use BAGArt\TelegramBot\Outbound\OutboundTask;

/**
 * Default ordering strategy.
 *
 * Key resolution priority:
 *   1. Explicit $task->orderingKey — highest priority (processor set the key itself,
 *      e.g. 'chat_id:game_session' for isolating game sessions).
 *   2. Fallback to chat_id from $task->dtoData (if DTO contains it).
 *   3. null — no ordering (broadcast, sendPhoto to all).
 *
 * @see todo.md §3.5.
 */
final class DefaultOrderingStrategy implements OrderingStrategyContract
{
    public function keyFor(OutboundTask $task): ?string
    {
        // 1. Explicit orderingKey from task — highest priority.
        if ($task->orderingKey !== null) {
            return $task->orderingKey;
        }

        // 2. Fallback to chat_id from payload DTO (if present).
        return $this->fromDtoData($task->dtoData);
    }

    public function keyForDto(array $dtoData): ?string
    {
        return $this->fromDtoData($dtoData);
    }

    /**
     * @param  array<string,mixed>  $dtoData
     */
    private function fromDtoData(array $dtoData): ?string
    {
        $chatId = $dtoData['chat_id'] ?? null;
        if ($chatId !== null) {
            return (string) $chatId;
        }

        return null;
    }
}
