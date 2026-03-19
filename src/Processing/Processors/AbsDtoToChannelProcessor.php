<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing\Processors;

use BAGArt\AsyncKernel\Contracts\ASKSchedulerContract;
use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\Contracts\Processing\Processors\TgTypeDTOProcessorContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\Processing\ErrorHandling\ProcessorErrorContext;
use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;
use BAGArt\TelegramBot\TgApiServices\TgEntityNamer;
use BAGArt\TelegramBot\TgBotSetup;

abstract class AbsDtoToChannelProcessor implements TgTypeDTOProcessorContract
{
    public static function build(
        TgServiceConfig $serviceConfig,
        TgBotSetup $botSetup,
        ?ASKSchedulerContract $scheduler = null,
        bool $onlyBasicInfo = true,
    ): static {
        return new static(
            namer: new TgEntityNamer(),
            onlyBasicInfo: $onlyBasicInfo ?? true,
        );
    }

    public function __construct(
        protected readonly TgEntityNamer $namer,
        protected readonly bool $onlyBasicInfo = true,
    ) {
    }

    public function support(
        TgApiTypeDTOContract $dto,
        TgBotConfig $botConfig,
        ?string $action = null,
    ): bool {
        return true;
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

    public function onException(
        ProcessorErrorContext $context,
    ): void {
    }

    public function dump(
        TgApiTypeDTOContract $dto,
        ?TgBotConfig $botConfig = null,
        ?string $action = null,
    ): string {
        try {
            $body = json_encode(
                $this->data(
                    dto: $dto,
                    botConfig: $botConfig,
                    action: $action,
                ),
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
                20
            );
        } catch (\JsonException $exception) {
            try {
                $body = "Unable dump dto to json! head:"
                    .json_encode(
                        $this->head(
                            dto: $dto,
                            botConfig: $botConfig,
                            action: $action,
                        ),
                        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
                        20
                    );
            } catch (\JsonException $exception) {
                $body = "bot_id: {$botConfig?->botId}; action: '$action'; Unable dump dto to json";
            }
        }

        return 'Incoming '.$dto::class.": $body";
    }

    public function data(
        TgApiTypeDTOContract $dto,
        ?TgBotConfig $botConfig = null,
        ?string $action = null,
    ): array {
        if ($this->onlyBasicInfo) {
            return $this->basic($dto, $botConfig, $action);
        }

        return $this->dtoToArray($dto);
    }

    public function basic(
        TgApiTypeDTOContract $dto,
        ?TgBotConfig $botConfig = null,
        ?string $action = null,
    ): array {
        if ($dto instanceof MessageTypeDTO) {
            return array_filter([
                'tg_bot_id' => $botConfig?->botId,
                'action' => $action,
                'message_id' => $dto->messageId,
                'chat_id' => $dto->chat->id,
                'chat' => $this->namer->name($dto->chat),
                'from_id' => $dto->from?->id,
                'from' => $dto->from ? $this->namer->name($dto->from) : null,
                'text' => $dto->text,
                'edit_date' => $dto->editDate,
                'reply_to_message_id' => $dto->replyToMessage?->messageId,
            ]);
        }
        return $this->head($dto, $botConfig, $action);
    }

    public function head(
        TgApiTypeDTOContract $dto,
        ?TgBotConfig $botConfig = null,
        ?string $action = null,
    ): array {
        return [
            'tg_bot_id' => $botConfig?->botId,
            'action' => $action,
            'dto' => $dto::class,
            'keys' => array_keys(
                array_filter(
                    (array)$dto,
                    fn ($item) => $item !== null,
                )
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function dtoToArray(object $dto): array
    {
        $result = [];
        $result['__class'] = $dto::class;
        foreach (get_object_vars($dto) as $key => $value) {
            if ($value === null) {
                continue;
            }

            if (is_object($value)) {
                $result[$key] = $this->dtoToArray($value);
            } elseif (is_array($value)) {
                $result[$key] = array_map(
                    fn ($item) => is_object($item) ? $this->dtoToArray($item) : $item,
                    $value,
                );
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
