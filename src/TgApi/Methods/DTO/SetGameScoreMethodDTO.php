<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Methods\DTO;

use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Methods\TgApiMethodsEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Use this method to set the score of the specified user in a game message. On success, if the message is not an inline message, the [Message](https://core.telegram.org/bots/api#message) is returned, otherwise _True_ is returned. Returns an error, if the new score is not greater than the user"s current score in the chat and _force_ is _False_.')]
#[See('https://core.telegram.org/bots/api#setgamescore')]
class SetGameScoreMethodDTO implements TgApiMethodDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('User identifier')]
        public int $userId,
        #[Description('New score, must be non-negative')]
        public int $score,
        #[Description('Pass _True_ if the high score is allowed to decrease. This can be useful when fixing mistakes or banning cheaters')]
        public ?bool $force = null,
        #[Description('Pass _True_ if the game message should not be automatically edited to include the current scoreboard')]
        public ?bool $disableEditMessage = null,
        #[Description('Required if _inline\_message\_id_ is not specified. Unique identifier for the target chat')]
        public ?int $chatId = null,
        #[Description('Required if _inline\_message\_id_ is not specified. Identifier of the sent message')]
        public ?int $messageId = null,
        #[Description('Required if _chat\_id_ and _message\_id_ are not specified. Identifier of the inline message')]
        public ?string $inlineMessageId = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function getReturnTypes(): array
    {
        return [
            MessageTypeDTO::class,
            'bool',
        ];
    }

    public static function tgApiEntity(): TgApiMethodsEnum
    {
        return TgApiMethodsEnum::setGameScore;
    }

    public static function tgEntityScope(): TgApiEntityScopeEnum
    {
        return TgApiEntityScopeEnum::Method;
    }

    /** @return TgApiProperty[] */
    public static function tgPropertyMetas(): array
    {
        $metaByProp = json_decode(
            <<<'XJSON'
{"user_id":{"property":"userId","tgPropName":"user_id","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"score":{"property":"score","tgPropName":"score","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"force":{"property":"force","tgPropName":"force","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"disable_edit_message":{"property":"disableEditMessage","tgPropName":"disable_edit_message","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"chat_id":{"property":"chatId","tgPropName":"chat_id","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"message_id":{"property":"messageId","tgPropName":"message_id","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"inline_message_id":{"property":"inlineMessageId","tgPropName":"inline_message_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false}}
XJSON,
            true,
            20,
            JSON_THROW_ON_ERROR
        );

        $result = [];
        foreach ($metaByProp as $tgPropName => $propertyMeta) {
            $result[$tgPropName] = new TgApiProperty(...$propertyMeta);
        }

        return $result;
    }
}
