<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\BotServices;

use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiDTOClientContract;
use BAGArt\TelegramBot\Contracts\BotServices\TgBotsSecretServiceContract;
use BAGArt\TelegramBot\TgApi\Methods\DTO\DeleteWebhookMethodDTO;
use BAGArt\TelegramBot\TgApi\Methods\DTO\GetWebhookInfoMethodDTO;
use BAGArt\TelegramBot\TgApi\Methods\DTO\SetWebhookMethodDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\WebhookInfoTypeDTO;

/**
 * @see https://core.telegram.org/bots/api#webhooks
 */
class WebhookManager
{
    public function __construct(
        private readonly TgBotApiDTOClientContract $tgDTOClient,
        private readonly ?TgBotsSecretServiceContract $secretService = null,
    ) {
    }

    /**
     * @see https://core.telegram.org/bots/api#getwebhookinfo
     */
    public function get(string $token): WebhookInfoTypeDTO
    {
        $response = $this->tgDTOClient->request(
            token: $token,
            dto: new GetWebhookInfoMethodDTO(),
        );
        $webhookInfo = $response->result;
        assert($webhookInfo instanceof WebhookInfoTypeDTO);

        return $webhookInfo;
    }

    /**
     * @see https://core.telegram.org/bots/api#setwebhook
     *
     * @param  string[]|null  $allowedUpdates
     */
    public function set(
        string $token,
        string $url,
        ?string $certificate = null,
        ?string $ipAddress = null,
        ?int $maxConnections = null,
        ?array $allowedUpdates = null,
        bool $dropPendingUpdates = false,
        ?string $secretToken = null,
    ): bool {
        if ($secretToken === null && $this->secretService) {
            $secretToken = $this->secretService->secret($token);
        }

        $response = $this->tgDTOClient->request(
            token: $token,
            dto: new SetWebhookMethodDTO(
                url: $url,
                certificate: $certificate,
                ipAddress: $ipAddress,
                maxConnections: $maxConnections,
                allowedUpdates: $allowedUpdates,
                dropPendingUpdates: $dropPendingUpdates,
                secretToken: $secretToken,
            ),
        );
        assert($response->result === true);

        return $response->result;
    }

    /**
     * @see https://core.telegram.org/bots/api#deletewebhook
     */
    public function delete(
        string $token,
        ?bool $dropPendingUpdates = null,
    ): bool {
        $response = $this->tgDTOClient->request(
            token: $token,
            dto: new DeleteWebhookMethodDTO(
                dropPendingUpdates: $dropPendingUpdates,
            ),
        );
        assert($response->result === true);

        return $response->result;
    }

    public function buildTextInfo(WebhookInfoTypeDTO $info): string
    {
        $url = $info->url ?: '(not set)';
        $secret = $info->hasCustomCertificate ? 'custom cert' : ($info->url ? 'yes' : '-');
        $pending = $info->pendingUpdateCount;
        $maxConn = $info->maxConnections ?? 'default (40)';
        $allowed = $info->allowedUpdates ? implode(', ', $info->allowedUpdates) : 'all';
        $ip = $info->ipAddress ?: 'default';

        $text = "URL:               $url\n";
        $text .= "Secret:            $secret\n";
        $text .= "Pending:           $pending\n";
        $text .= "Max connections:   $maxConn\n";
        $text .= "Allowed updates:   $allowed\n";
        $text .= "IP address:        $ip\n";

        if ($info->lastErrorMessage) {
            $errorTime = date('Y-m-d H:i:s', $info->lastErrorDate);
            $text .= "Error:             {$info->lastErrorMessage}\n";
            $text .= "Error at:          $errorTime\n";
        }

        return $text;
    }
}
