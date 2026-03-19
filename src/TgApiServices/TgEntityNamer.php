<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApiServices;

use BAGArt\TelegramBot\TgApi\Types\DTO\ChatTypeDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\UserTypeDTO;

class TgEntityNamer
{
    public function name(ChatTypeDTO|UserTypeDTO $entity): string
    {
        if (isset($entity->username) && strlen($entity->username) > 0) {
            return '@'.$entity->username;
        }

        if (isset($entity->title) && strlen($entity->title) > 0) {
            return $entity->title;
        }

        $isBot = ($entity instanceof UserTypeDTO && $entity->isBot);
        $bot = $isBot ? '🤖' : null;

        $firstName = $entity->firstName ?? '';
        $lastName = $entity->lastName ?? '';
        $name = trim($firstName.' '.$lastName);

        if (strlen($name) > 0) {
            return "$bot$name";
        }

        if ($bot !== null) {
            return "[$bot{$entity->id}]";
        }

        return "[{$entity->id}]";
    }
}
