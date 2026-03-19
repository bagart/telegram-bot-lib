<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Methods\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\TgApi\Methods\TgApiMethodsEnum;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Use this method to decline a suggested post in a direct messages chat. The bot must have the "can\_manage\_direct\_messages" administrator right in the corresponding channel chat. Returns _True_ on success.')]
#[See('https://core.telegram.org/bots/api#declinesuggestedpost')]
class DeclineSuggestedPostMethodDTO implements TgApiMethodDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Unique identifier for the target direct messages chat')]
        public int $chatId,
        #[Description('Identifier of a suggested post message to decline')]
        public int $messageId,
        #[Description('Comment for the creator of the suggested post; 0-128 characters')]
        public ?string $comment = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function getReturnTypes(): array
    {
        return [
            'bool',
        ];
    }

    public static function tgApiEntity(): TgApiMethodsEnum
    {
        return TgApiMethodsEnum::declineSuggestedPost;
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
{"chat_id":{"property":"chatId","tgPropName":"chat_id","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"message_id":{"property":"messageId","tgPropName":"message_id","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"comment":{"property":"comment","tgPropName":"comment","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false}}
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
