<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\BotServices;

use BAGArt\TelegramBot\Contracts\TgBotRegistry\TgBotRegistryContract;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;

class BotSecretRegistry implements TgBotRegistryContract
{
    /** @var array<string, BotSecretDTO> */
    private array $bots = [];

    /** @var array<string, string[]> secret => botId[] */
    private array $secrets = [];

    public function __construct(
        private TgBotLogWrapper $logger,
    ) {
    }

    public function register(BotSecretDTO $bot): self
    {
        $botId = $bot->botId();
        if (isset($this->bots[$botId])) {
            $this->logger->warning("BotRegistry: Overwrite Bot token: '{$bot->botId()}'");
            unset($this->secrets[$this->bots[$botId]->secret()][$botId]);
        }
        $this->bots[$botId] = $bot;
        $this->secrets[$bot->secret() ?? ''][$botId] = $botId;

        return $this;
    }

    public function getBot(string $botId): ?BotSecretDTO
    {
        return $this->bots[$botId] ?? null;
    }

    /**
     * @return \Generator|BotSecretDTO[]
     */
    public function getBotsBySecret(?string $secret): \Generator
    {
        foreach ($this->getBotIdsBySecret($secret) as $botId) {
            yield $this->bots[$botId];
        }
    }

    /**
     * @return \Generator|string[]
     */
    public function getBotIdsBySecret(?string $secret): \Generator
    {
        foreach ($this->secrets[$secret] ?? [] as $botId) {
            yield $botId;
        }
    }

    public function getBotCount(): int
    {
        return count($this->bots);
    }

    public function has(string $botId): bool
    {
        return isset($this->bots[$botId]);
    }
}
