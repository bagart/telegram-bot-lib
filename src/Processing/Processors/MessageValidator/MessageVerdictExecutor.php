<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing\Processors\MessageValidator;

use BAGArt\AsyncKernel\Wrappers\ASKLogWrapper;
use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Contracts\Outbound\TgSenderContract;
use BAGArt\TelegramBot\TgApi\Methods\DTO\BanChatMemberMethodDTO;
use BAGArt\TelegramBot\TgApi\Methods\DTO\DeleteMessageMethodDTO;
use BAGArt\TelegramBot\TgApi\Methods\DTO\RestrictChatMemberMethodDTO;
use BAGArt\TelegramBot\TgApi\Methods\DTO\SendMessageMethodDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\ChatPermissionsTypeDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;
use Throwable;

class MessageVerdictExecutor
{
    public function __construct(
        private readonly TgSenderContract $sender,
        private readonly ASKLogWrapper $logger,
    ) {
    }

    public function execute(
        MessageTypeDTO $dto,
        string $botId,
        MessageValidationVerdict $verdict,
        TgBotConfig $botConfig,
    ): void {
        $this->logger->info('MessageValidatorProcessor: verdict applied', [
            'botId' => $botId,
            'chatId' => $dto->chat->id,
            'userId' => $dto->from->id,
            'messageId' => $dto->messageId,
            'action' => $verdict->action->value,
            'reason' => $verdict->reason,
            'matchedRule' => $verdict->matchedRule,
        ]);

        if ($verdict->deleteMessage) {
            $this->deleteMessage($dto, $botConfig);
        }

        $this->applyAction($dto, $verdict, $botConfig);

        if ($verdict->notifyChat) {
            $this->sendWarning($dto, $verdict, $botConfig);
        }
    }

    private function deleteMessage(MessageTypeDTO $dto, TgBotConfig $botConfig): void
    {
        try {
            $this->sender->send($botConfig, new DeleteMessageMethodDTO(
                chatId: $dto->chat->id,
                messageId: $dto->messageId,
            ));
        } catch (Throwable $e) {
            $this->logger->error('MessageValidatorProcessor: failed to delete message', [
                'chatId' => $dto->chat->id,
                'messageId' => $dto->messageId,
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function applyAction(MessageTypeDTO $dto, MessageValidationVerdict $verdict, TgBotConfig $botConfig): void
    {
        $userId = (int)$dto->from->id;

        try {
            match ($verdict->action) {
                MessageVerdictActionEnum::Restrict => $this->restrictUser(
                    $dto,
                    $userId,
                    $verdict->restrictDuration,
                    $botConfig
                ),
                MessageVerdictActionEnum::Ban => $this->banUser($dto, $userId, $botConfig),
            };
        } catch (Throwable $e) {
            $this->logger->error('MessageValidatorProcessor: failed to apply action', [
                'chatId' => $dto->chat->id,
                'userId' => $userId,
                'action' => $verdict->action->value,
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function restrictUser(
        MessageTypeDTO $dto,
        int $userId,
        ?int $restrictDuration,
        TgBotConfig $botConfig
    ): void {
        $this->sender->send($botConfig, new RestrictChatMemberMethodDTO(
            chatId: $dto->chat->id,
            userId: $userId,
            permissions: new ChatPermissionsTypeDTO(
                canSendMessages: false,
                canSendAudios: false,
                canSendDocuments: false,
                canSendPhotos: false,
                canSendVideos: false,
                canSendVideoNotes: false,
                canSendVoiceNotes: false,
                canSendPolls: false,
                canSendOtherMessages: false,
                canAddWebPagePreviews: false,
                canChangeInfo: false,
                canInviteUsers: false,
                canPinMessages: false,
                canManageTopics: false,
            ),
            untilDate: $restrictDuration !== null
                ? time() + $restrictDuration
                : null,
        ));
    }

    private function banUser(MessageTypeDTO $dto, int $userId, TgBotConfig $botConfig): void
    {
        $this->sender->send($botConfig, new BanChatMemberMethodDTO(
            chatId: $dto->chat->id,
            userId: $userId,
            revokeMessages: true,
        ));
    }

    private function sendWarning(MessageTypeDTO $dto, MessageValidationVerdict $verdict, TgBotConfig $botConfig): void
    {
        $userName = $dto->from->username !== null
            ? '@'.$dto->from->username
            : $dto->from->firstName;

        $actionLabel = match ($verdict->action) {
            MessageVerdictActionEnum::Restrict => 'restricted',
            MessageVerdictActionEnum::Ban => 'banned',
        };

        if ($verdict->warningMessage !== null) {
            $message = str_replace(
                ['{user}', '{reason}', '{action}'],
                [$userName, $verdict->reason, $actionLabel],
                $verdict->warningMessage,
            );
        } else {
            $message = "\u{26a0}\u{fe0f} User {$userName} {$actionLabel} for {$verdict->reason}.";
        }

        try {
            $this->sender->send($botConfig, new SendMessageMethodDTO(
                chatId: $dto->chat->id,
                text: $message,
            ));
        } catch (Throwable $e) {
            $this->logger->error('MessageValidatorProcessor: failed to send warning', [
                'chatId' => $dto->chat->id,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
