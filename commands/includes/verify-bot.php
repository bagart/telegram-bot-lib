<?php

use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiDTOClientContract;
use BAGArt\TelegramBot\TgApi;
use BAGArt\TelegramBot\TgApi\Types\DTO\UserTypeDTO;

/**
 * Verify bot token by calling getMe and printing bot info.
 *
 * @param  TgBotApiDTOClientContract  $dtoClient  Telegram API DTO client
 * @param  string  $token  Telegram Bot API token
 * @return UserTypeDTO                           verified bot user
 *
 * Exits with code 1 on failure.
 */
function verifyBot(TgBotApiDTOClientContract $dtoClient, string $token): UserTypeDTO
{
    try {
        $response = $dtoClient->request($token, new TgApi\Methods\DTO\GetMeMethodDTO());
        $user = $response->result;
        assert($user instanceof UserTypeDTO);

        echo "Bot verified: @{$user->username} (".trim($user->firstName.' '.$user->lastName).");\n";

        return $user;
    } catch (\Throwable $e) {
        echo 'Failed to connect to Telegram: '.$e::class."{$e->getMessage()};\n{$e->getTraceAsString()}\n";
        exit(1);
    }
}
