<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TypeDTOProcessor\Processors\MessageValidator;

use BAGArt\TelegramBot\ApiCommunication\TgBotApiDTOClient;
use BAGArt\TelegramBot\Contracts\ApiCommunication\Async\SchedulerContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\Contracts\TgUpdateProcessor\TgTypeDTOProcessorContract;
use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;
use BAGArt\TelegramBot\TgUpdateConfig;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;

class MessageValidatorProcessor implements TgTypeDTOProcessorContract
{
    public static function build(
        ?TgUpdateConfig $config = null,
        ?TgBotLogWrapper $logger = null,
    ): self {
        return new static(
            ruleRegistry: new MessageValidationRuleRegistry(),
            executor: new MessageVerdictExecutor(
                dtoClient: TgBotApiDTOClient::build(),
                logger: TgBotLogWrapper::build(),
                token: $config?->bot->token,
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
        TgUpdateConfig $config,
        ?string $action = null,
    ): bool {
        return $dto instanceof MessageTypeDTO
            && $dto->from !== null
            && $dto->from->isBot === false
            && !in_array($dto->chat->type->value, ['private', 'channel'], true);
    }

    public function process(
        TgApiTypeDTOContract $dto,
        string $botId,
        TgUpdateConfig $config,
        ?string $action = null,
        ?SchedulerContract $scheduler = null,
    ): void {
        assert($dto instanceof MessageTypeDTO);

        $verdict = $this->findVerdict($dto);
        if ($verdict === null) {
            return;
        }

        $this->antiSpamLogger?->log($dto, $botId, $verdict);

        $this->executor->execute($dto, $botId, $verdict);
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
}
