<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object contains information about a poll.')]
#[See('https://core.telegram.org/bots/api#poll')]
class PollTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Unique poll identifier')]
        public string $id,
        #[Description('Poll question, 1-300 characters')]
        public string $question,
        #[Description('List of poll options')]
        public array $options,
        #[Description('Total number of users that voted in the poll')]
        public int $totalVoterCount,
        #[Description('_True_, if the poll is closed')]
        public bool $isClosed,
        #[Description('_True_, if the poll is anonymous')]
        public bool $isAnonymous,
        #[Description('Poll type, currently can be “regular” or “quiz”')]
        public \BAGArt\TelegramBot\TgApi\Types\Enum\PollPropTypeEnum $type,
        #[Description('_True_, if the poll allows multiple answers')]
        public bool $allowsMultipleAnswers,
        #[Description('Special entities that appear in the _question_. Currently, only custom emoji entities are allowed in poll questions')]
        public ?array $questionEntities = null,
        #[Description('0-based identifier of the correct answer option. Available only for polls in the quiz mode, which are closed, or was sent (not forwarded) by the bot or to the private chat with the bot.')]
        public ?int $correctOptionId = null,
        #[Description('Text that is shown when a user chooses an incorrect answer or taps on the lamp icon in a quiz-style poll, 0-200 characters')]
        public ?string $explanation = null,
        #[Description('Special entities like usernames, URLs, bot commands, etc. that appear in the _explanation_')]
        public ?array $explanationEntities = null,
        #[Description('Amount of time in seconds the poll will be active after creation')]
        public ?int $openPeriod = null,
        #[Description('Point in time (Unix timestamp) when the poll will be automatically closed')]
        public ?int $closeDate = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::Poll;
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
{"id":{"property":"id","tgPropName":"id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"question":{"property":"question","tgPropName":"question","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"question_entities":{"property":"questionEntities","tgPropName":"question_entities","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\MessageEntityTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"MessageEntity"}}],"nullable":true,"required":false},"options":{"property":"options","tgPropName":"options","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\PollOptionTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"PollOption"}}],"nullable":false,"required":true},"total_voter_count":{"property":"totalVoterCount","tgPropName":"total_voter_count","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"is_closed":{"property":"isClosed","tgPropName":"is_closed","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":false,"required":true},"is_anonymous":{"property":"isAnonymous","tgPropName":"is_anonymous","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":false,"required":true},"type":{"property":"type","tgPropName":"type","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\Enum\\PollPropTypeEnum"],"tgTypes":[{"type":"str","literal":"regular"},{"type":"str","literal":"quiz"}],"nullable":false,"required":true},"allows_multiple_answers":{"property":"allowsMultipleAnswers","tgPropName":"allows_multiple_answers","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":false,"required":true},"correct_option_id":{"property":"correctOptionId","tgPropName":"correct_option_id","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"explanation":{"property":"explanation","tgPropName":"explanation","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"explanation_entities":{"property":"explanationEntities","tgPropName":"explanation_entities","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\MessageEntityTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"MessageEntity"}}],"nullable":true,"required":false},"open_period":{"property":"openPeriod","tgPropName":"open_period","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"close_date":{"property":"closeDate","tgPropName":"close_date","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false}}
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
