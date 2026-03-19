<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Describes a video to post as a story.')]
#[See('https://core.telegram.org/bots/api#inputstorycontentvideo')]
class InputStoryContentVideoTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('The video to post as a story. The video must be of the size 720x1280, streamable, encoded with H.265 codec, with key frames added each second in the MPEG4 format, and must not exceed 30 MB. The video can"t be reused and can only be uploaded as a new file, so you can pass “attach://<file\_attach\_name>” if the video was uploaded using multipart/form-data under <file\_attach\_name>. [More information on Sending Files »](https://core.telegram.org/bots/api#sending-files)')]
        public string $video,
        #[Description('Type of the content, must be _video_')]
        public string $type = 'video',
        #[Description('Precise duration of the video in seconds; 0-60')]
        public ?string $duration = null,
        #[Description('Timestamp in seconds of the frame that will be used as the static cover for the story. Defaults to 0.0.')]
        public ?string $coverFrameTimestamp = null,
        #[Description('Pass _True_ if the video has no sound')]
        public ?bool $isAnimation = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::InputStoryContentVideo;
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
{"type":{"property":"type","tgPropName":"type","types":["string"],"tgTypes":[{"type":"str","literal":"video"}],"nullable":false,"required":true},"video":{"property":"video","tgPropName":"video","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"duration":{"property":"duration","tgPropName":"duration","types":["string"],"tgTypes":[{"type":"float"}],"nullable":true,"required":false},"cover_frame_timestamp":{"property":"coverFrameTimestamp","tgPropName":"cover_frame_timestamp","types":["string"],"tgTypes":[{"type":"float"}],"nullable":true,"required":false},"is_animation":{"property":"isAnimation","tgPropName":"is_animation","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false}}
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
