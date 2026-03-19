<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('The message was originally sent to a channel chat.')]
#[See('https://core.telegram.org/bots/api#messageoriginchannel')]
class MessageOriginChannelTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Date the message was sent originally in Unix time')]
        public int $date,
        #[Description('Channel chat to which the message was originally sent')]
        public ChatTypeDTO $chat,
        #[Description('Unique message identifier inside the chat')]
        public int $messageId,
        #[Description('Type of the message origin, always “channel”')]
        public string $type = 'channel',
        #[Description('Signature of the original post author')]
        public ?string $authorSignature = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::MessageOriginChannel;
    }

    public static function tgEntityScope(): TgApiEntityScopeEnum
    {
        return TgApiEntityScopeEnum::Type;
    }

    /** @return TgApiProperty[] */
    public static function tgPropertyMetas(): array
    {
        $metaByProp = json_decode(
            <<<'XJSON'
{"type":{"property":"type","tgPropName":"type","types":["string"],"tgTypes":[{"type":"str","literal":"channel"}],"nullable":false,"required":true},"date":{"property":"date","tgPropName":"date","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"chat":{"property":"chat","tgPropName":"chat","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ChatTypeDTO"],"tgTypes":[{"type":"api-type","name":"Chat"}],"nullable":false,"required":true},"message_id":{"property":"messageId","tgPropName":"message_id","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"author_signature":{"property":"authorSignature","tgPropName":"author_signature","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false}}
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
