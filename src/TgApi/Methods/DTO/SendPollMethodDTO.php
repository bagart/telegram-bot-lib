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
#[Description('Use this method to send a native poll. On success, the sent [Message](https://core.telegram.org/bots/api#message) is returned.')]
#[See('https://core.telegram.org/bots/api#sendpoll')]
class SendPollMethodDTO implements TgApiMethodDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Unique identifier for the target chat or username of the target channel (in the format `@channelusername`). Polls can"t be sent to channel direct messages chats.')]
        public string $chatId,
        #[Description('Poll question, 1-300 characters')]
        public string $question,
        #[Description('An array of 2-12 answer options')]
        public array $options,
        #[Description('Unique identifier of the business connection on behalf of which the message will be sent')]
        public ?string $businessConnectionId = null,
        #[Description('Unique identifier for the target message thread (topic) of a forum; for forum supergroups and private chats of bots with forum topic mode enabled only')]
        public ?int $messageThreadId = null,
        #[Description('Mode for parsing entities in the question. See [formatting options](https://core.telegram.org/bots/api#formatting-options) for more details. Currently, only custom emoji entities are allowed')]
        public ?\BAGArt\TelegramBot\TgApi\Methods\Enum\QuestionParseModeEnum $questionParseMode = null,
        #[Description('An array of special entities that appear in the poll question. It can be specified instead of _question\_parse\_mode_')]
        public ?array $questionEntities = null,
        #[Description('_True_, if the poll needs to be anonymous, defaults to _True_')]
        public ?bool $isAnonymous = null,
        #[Description('Poll type, “quiz” or “regular”, defaults to “regular”')]
        public ?\BAGArt\TelegramBot\TgApi\Methods\Enum\SendPollPropTypeEnum $type = null,
        #[Description('_True_, if the poll allows multiple answers, ignored for polls in quiz mode, defaults to _False_')]
        public ?bool $allowsMultipleAnswers = null,
        #[Description('0-based identifier of the correct answer option, required for polls in quiz mode')]
        public ?int $correctOptionId = null,
        #[Description('Text that is shown when a user chooses an incorrect answer or taps on the lamp icon in a quiz-style poll, 0-200 characters with at most 2 line feeds after entities parsing')]
        public ?string $explanation = null,
        #[Description('Mode for parsing entities in the explanation. See [formatting options](https://core.telegram.org/bots/api#formatting-options) for more details.')]
        public ?\BAGArt\TelegramBot\TgApi\Methods\Enum\ExplanationParseModeEnum $explanationParseMode = null,
        #[Description('An array of special entities that appear in the poll explanation. It can be specified instead of _explanation\_parse\_mode_')]
        public ?array $explanationEntities = null,
        #[Description('Amount of time in seconds the poll will be active after creation, 5-600. Can"t be used together with _close\_date_.')]
        public ?int $openPeriod = null,
        #[Description('Point in time (Unix timestamp) when the poll will be automatically closed. Must be at least 5 and no more than 600 seconds in the future. Can"t be used together with _open\_period_.')]
        public ?int $closeDate = null,
        #[Description('Pass _True_ if the poll needs to be immediately closed. This can be useful for poll preview.')]
        public ?bool $isClosed = null,
        #[Description('Sends the message [silently](https://telegram.org/blog/channels-2-0#silent-messages). Users will receive a notification with no sound.')]
        public ?bool $disableNotification = null,
        #[Description('Protects the contents of the sent message from forwarding and saving')]
        public ?bool $protectContent = null,
        #[Description('Pass _True_ to allow up to 1000 messages per second, ignoring [broadcasting limits](https://core.telegram.org/bots/faq#how-can-i-message-all-of-my-bot-39s-subscribers-at-once) for a fee of 0.1 Telegram Stars per message. The relevant Stars will be withdrawn from the bot"s balance')]
        public ?bool $allowPaidBroadcast = null,
        #[Description('Unique identifier of the message effect to be added to the message; for private chats only')]
        public ?string $messageEffectId = null,
        #[Description('Description of the message to reply to')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\ReplyParametersTypeDTO $replyParameters = null,
        #[Description('Additional interface options. An object for an [inline keyboard](https://core.telegram.org/bots/features#inline-keyboards), [custom reply keyboard](https://core.telegram.org/bots/features#keyboards), instructions to remove a reply keyboard or to force a reply from the user')]
        public \BAGArt\TelegramBot\TgApi\Types\DTO\ForceReplyTypeDTO|\BAGArt\TelegramBot\TgApi\Types\DTO\InlineKeyboardMarkupTypeDTO|\BAGArt\TelegramBot\TgApi\Types\DTO\ReplyKeyboardMarkupTypeDTO|\BAGArt\TelegramBot\TgApi\Types\DTO\ReplyKeyboardRemoveTypeDTO|null $replyMarkup = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function getReturnTypes(): array
    {
        return [
            MessageTypeDTO::class,
        ];
    }

    public static function tgApiEntity(): TgApiMethodsEnum
    {
        return TgApiMethodsEnum::sendPoll;
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
{"business_connection_id":{"property":"businessConnectionId","tgPropName":"business_connection_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"chat_id":{"property":"chatId","tgPropName":"chat_id","types":["string"],"tgTypes":[{"type":"int32"},{"type":"str"}],"nullable":false,"required":true},"message_thread_id":{"property":"messageThreadId","tgPropName":"message_thread_id","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"question":{"property":"question","tgPropName":"question","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"question_parse_mode":{"property":"questionParseMode","tgPropName":"question_parse_mode","types":["?\\BAGArt\\TelegramBot\\TgApi\\Methods\\Enum\\QuestionParseModeEnum"],"tgTypes":[{"type":"str","literal":"HTML"},{"type":"str","literal":"MarkdownV2"},{"type":"str","literal":"Markdown"}],"nullable":true,"required":false},"question_entities":{"property":"questionEntities","tgPropName":"question_entities","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\MessageEntityTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"MessageEntity"}}],"nullable":true,"required":false},"options":{"property":"options","tgPropName":"options","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\InputPollOptionTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"InputPollOption"}}],"nullable":false,"required":true},"is_anonymous":{"property":"isAnonymous","tgPropName":"is_anonymous","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"type":{"property":"type","tgPropName":"type","types":["?\\BAGArt\\TelegramBot\\TgApi\\Methods\\Enum\\SendPollPropTypeEnum"],"tgTypes":[{"type":"str","literal":"quiz"},{"type":"str","literal":"regular"}],"nullable":true,"required":false},"allows_multiple_answers":{"property":"allowsMultipleAnswers","tgPropName":"allows_multiple_answers","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"correct_option_id":{"property":"correctOptionId","tgPropName":"correct_option_id","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"explanation":{"property":"explanation","tgPropName":"explanation","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"explanation_parse_mode":{"property":"explanationParseMode","tgPropName":"explanation_parse_mode","types":["?\\BAGArt\\TelegramBot\\TgApi\\Methods\\Enum\\ExplanationParseModeEnum"],"tgTypes":[{"type":"str","literal":"HTML"},{"type":"str","literal":"MarkdownV2"},{"type":"str","literal":"Markdown"}],"nullable":true,"required":false},"explanation_entities":{"property":"explanationEntities","tgPropName":"explanation_entities","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\MessageEntityTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"MessageEntity"}}],"nullable":true,"required":false},"open_period":{"property":"openPeriod","tgPropName":"open_period","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"close_date":{"property":"closeDate","tgPropName":"close_date","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"is_closed":{"property":"isClosed","tgPropName":"is_closed","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"disable_notification":{"property":"disableNotification","tgPropName":"disable_notification","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"protect_content":{"property":"protectContent","tgPropName":"protect_content","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"allow_paid_broadcast":{"property":"allowPaidBroadcast","tgPropName":"allow_paid_broadcast","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"message_effect_id":{"property":"messageEffectId","tgPropName":"message_effect_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"reply_parameters":{"property":"replyParameters","tgPropName":"reply_parameters","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ReplyParametersTypeDTO"],"tgTypes":[{"type":"api-type","name":"ReplyParameters"}],"nullable":true,"required":false},"reply_markup":{"property":"replyMarkup","tgPropName":"reply_markup","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ForceReplyTypeDTO","\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\InlineKeyboardMarkupTypeDTO","\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ReplyKeyboardMarkupTypeDTO","\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ReplyKeyboardRemoveTypeDTO"],"tgTypes":[{"type":"api-type","name":"InlineKeyboardMarkup"},{"type":"api-type","name":"ReplyKeyboardMarkup"},{"type":"api-type","name":"ReplyKeyboardRemove"},{"type":"api-type","name":"ForceReply"}],"nullable":true,"required":false}}
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
