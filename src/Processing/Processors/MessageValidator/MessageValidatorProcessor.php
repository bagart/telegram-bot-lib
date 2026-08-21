<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing\Processors\MessageValidator;

use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Contracts\Processing\Processors\TgTypeDTOProcessorContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\Processing\BotProcessorContext;
use BAGArt\TelegramBot\Processing\ErrorHandling\ProcessorErrorContext;
use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;

class MessageValidatorProcessor implements TgTypeDTOProcessorContract
{
    public static function build(
        BotProcessorContext $context,
    ): self {
        $ruleRegistry = $context->messageRules ?? MessageValidationRuleRegistry::withCoreRules();

        return new static(
            ruleRegistry: $ruleRegistry,
            executor: new MessageVerdictExecutor(
                sender: $context->tgSender
                    ?? $context->tgApiCaller,
                logger: $context->logger,
            ),
            antiSpamLogger: new AntiSpamLogger(),
        );
    }

    public function __construct(
        private readonly MessageValidationRuleRegistry $ruleRegistry,
        private readonly MessageVerdictExecutor $executor,
        private readonly ?AntiSpamLogger $antiSpamLogger = null,
    ) {
    }

    public function support(
        TgApiTypeDTOContract $dto,
        TgBotConfig $botConfig,
        ?string $action = null,
    ): bool {
        return $dto instanceof MessageTypeDTO
            && $dto->from !== null
            && $dto->from->isBot === false
            && !in_array($dto->chat->type->value, ['private', 'channel'], true);
    }

    public function isStrictOrdered(
        TgApiTypeDTOContract $dto,
        TgBotConfig $botConfig,
        ?string $action = null,
    ): bool {
        return false;
    }

    public function isNeedUpdateDTO(): bool
    {
        return false;
    }

    public function executionKey(
        \BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract $dto,
    ): ?string {
        return null;
    }

    public function process(
        TgApiTypeDTOContract $dto,
        TgBotConfig $botConfig,
        ?string $action = null,
        ?TgApiTypeDTOContract $updateDto = null,
    ): void {
        assert($dto instanceof MessageTypeDTO);

        $verdict = $this->findVerdict($dto);
        if ($verdict === null) {
            return;
        }

        $this->antiSpamLogger?->log(
            $dto,
            $botConfig->botId,
            $verdict
        );

        $this->executor->execute(
            $dto,
            $botConfig->botId,
            $verdict,
            $botConfig,
        );
    }

    private function findVerdict(MessageTypeDTO $dto): ?MessageValidationVerdict
    {
        $bestVerdict = null;

        foreach ($this->ruleRegistry->rules() as $rule) {
            $verdict = $rule->validate($dto);
            if ($verdict !== null) {
                if ($bestVerdict === null || $verdict->priority > $bestVerdict->priority) {
                    $bestVerdict = $verdict;
                }
            }
        }

        return $bestVerdict;
    }

    public function onException(
        ProcessorErrorContext $context,
    ): void {
    }
}
