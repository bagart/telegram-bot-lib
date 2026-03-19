<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApiServices;

use BAGArt\TelegramBot\TgApi\Types\DTO\ChatTypeDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\UserTypeDTO;

class TgEntityNamer
{
    public function name(ChatTypeDTO|UserTypeDTO $entity): string
    {
        if (strlen($entity->username)) {
            return '@'.$entity->username;
        }

        if (isset($entity->title)) {
            return $entity->title;
        }

        $bot = $entity->isBot ? '🤖' : null;
        $name = trim($entity->firstName.' '.$entity->lastName);
        if (strlen($name)) {
            return "$bot$name";
        }

        return "[$bot{$entity->id}]";
    }
}
