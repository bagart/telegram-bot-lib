<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('The paid media to send is a video.')]
#[See('https://core.telegram.org/bots/api#inputpaidmediavideo')]
class InputPaidMediaVideoTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('File to send. Pass a file\_id to send a file that exists on the Telegram servers (recommended), pass an HTTP URL for Telegram to get a file from the Internet, or pass “attach://<file\_attach\_name>” to upload a new one using multipart/form-data under <file\_attach\_name> name. [More information on Sending Files »](https://core.telegram.org/bots/api#sending-files)')]
        public string $media,
        #[Description('Type of the media, must be _video_')]
        public string $type = 'video',
        #[Description('Thumbnail of the file sent; can be ignored if thumbnail generation for the file is supported server-side. The thumbnail should be in JPEG format and less than 200 kB in size. A thumbnail"s width and height should not exceed 320. Ignored if the file is not uploaded using multipart/form-data. Thumbnails can"t be reused and can be only uploaded as a new file, so you can pass “attach://<file\_attach\_name>” if the thumbnail was uploaded using multipart/form-data under <file\_attach\_name>. [More information on Sending Files »](https://core.telegram.org/bots/api#sending-files)')]
        public ?string $thumbnail = null,
        #[Description('Cover for the video in the message. Pass a file\_id to send a file that exists on the Telegram servers (recommended), pass an HTTP URL for Telegram to get a file from the Internet, or pass “attach://<file\_attach\_name>” to upload a new one using multipart/form-data under <file\_attach\_name> name. [More information on Sending Files »](https://core.telegram.org/bots/api#sending-files)')]
        public ?string $cover = null,
        #[Description('Start timestamp for the video in the message')]
        public ?int $startTimestamp = null,
        #[Description('Video width')]
        public ?int $width = null,
        #[Description('Video height')]
        public ?int $height = null,
        #[Description('Video duration in seconds')]
        public ?int $duration = null,
        #[Description('Pass _True_ if the uploaded video is suitable for streaming')]
        public ?bool $supportsStreaming = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::InputPaidMediaVideo;
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
{"type":{"property":"type","tgPropName":"type","types":["string"],"tgTypes":[{"type":"str","literal":"video"}],"nullable":false,"required":true},"media":{"property":"media","tgPropName":"media","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"thumbnail":{"property":"thumbnail","tgPropName":"thumbnail","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"cover":{"property":"cover","tgPropName":"cover","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"start_timestamp":{"property":"startTimestamp","tgPropName":"start_timestamp","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"width":{"property":"width","tgPropName":"width","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"height":{"property":"height","tgPropName":"height","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"duration":{"property":"duration","tgPropName":"duration","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"supports_streaming":{"property":"supportsStreaming","tgPropName":"supports_streaming","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false}}
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
