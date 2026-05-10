<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TypeDTOProcessor\Processors\MessageValidator;

use BAGArt\TelegramBot\Contracts\DbLogger\TgDbLoggerWrapperContract;
use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;
use Throwable;

class AntiSpamLogger
{
    public function __construct(
        private readonly ?TgBotLogWrapper $logger = null,
        private readonly ?TgDbLoggerWrapperContract $dbLogger = null,
        private readonly ?string $path = 'storage/logs/antispam',
    ) {
    }

    public function log(MessageTypeDTO $dto, string $botId, MessageValidationVerdict $verdict): void
    {
        $text = $this->normalizeText($dto->text ?? $dto->caption ?? '');

        $entry = [
            'ts' => date('Y-m-d\TH:i:s'),
            'bot' => (int)$botId,
            'chat' => $dto->chat->id,
            'user' => $dto->from->id,
            'username' => $dto->from->username ?? '',
            'msg' => $dto->messageId,
            'rule' => $verdict->matchedRule,
            'reason' => $verdict->reason,
            'level' => $verdict->priority,
            'action' => $verdict->action->value,
            'text' => mb_substr($text, 0, 200),
        ];

        $line = json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $this->logger?->info('AntiSpam: '.$line);

        if ($this->path) {
            $this->writeToFile($dto->chat->id, $line);
        }

        $this->dbLogger?->log($dto, [
            'bot_id' => $botId,
            'rule' => $verdict->matchedRule,
            'reason' => $verdict->reason,
            'level' => $verdict->priority,
            'action' => $verdict->action->value,
        ]);
    }

    private function normalizeText(string $text): string
    {
        return preg_replace('/\s+/u', ' ', trim($text));
    }

    private function writeToFile(int|string $chatId, string $line): void
    {
        $filename = sprintf('%s/%s-%s.log', $this->path, $chatId, date('y-m'));

        try {
            if (!is_dir($this->path)) {
                mkdir($this->path, 0775, true);
            }
            file_put_contents($filename, $line."\n", FILE_APPEND | LOCK_EX);
        } catch (Throwable $e) {
            if (!$this->logger) {
                throw $e;
            }
            $this->logger?->error('AntiSpam: failed to write log file', [
                'file' => $filename,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
