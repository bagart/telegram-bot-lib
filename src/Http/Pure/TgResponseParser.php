<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Http\Pure;

use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiReturnParserContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\Contracts\TgApiServices\TgApiDTOMapperContract;
use BAGArt\TelegramBot\Exceptions\TgApi\TgApiException;
use BAGArt\TelegramBot\Exceptions\TgApi\TgBadRequestException;
use BAGArt\TelegramBot\Exceptions\TgApi\TgFloodWaitException;
use BAGArt\TelegramBot\Exceptions\TgBotTechnicalWithEntityException;
use BAGArt\TelegramBot\Exceptions\TgUnexpectedDataFormatException;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;

class TgResponseParser implements TgBotApiReturnParserContract
{
    public function __construct(
        private readonly TgApiDTOMapperContract $tgApiDTOMapper,
        private readonly TgBotLogWrapper $logger,
    ) {
    }

    public function parse(
        TgApiMethodDTOContract|string $dto,
        array $response,
    ): TgApiResponse {
        assert(is_a($dto, TgApiMethodDTOContract::class));
        $this->checkResponse($dto, $response);
        $returnTypes = $dto::getReturnTypes();

        foreach ($returnTypes as $returnType) {
            if (
                in_array($returnType, [
                    'array',//TgApiTypeDTOContract[]
                    'bool',//deleteMessage, setWebhook, ...(80+)
                    'string',//createInvoiceLink, exportChatInviteLink
                    'int',//getChatMemberCount
                ])
                && !is_a($returnType, TgApiTypeDTOContract::class)
            ) {
                $this->logger->warning(
                    'Unexpected api result or wrong code implementation: Not Expected return types of '
                    .$dto::class
                    .': '.$returnType
                );
            }
        }

        $result = null;
        $isOk = false;
        foreach ($returnTypes as $returnType) {
            try {
                $result = $this->buildInternal(
                    dto: $dto,
                    expectType: $returnType,
                    returnLevel: $response['result'],
                );
                $isOk = true;
                break;
            } catch (TgBotTechnicalWithEntityException $buildException) {
                //try to check next
            }
        }

        if (!$isOk && isset($buildException)) {
            throw $buildException;
        }

        assert($this->checkReturnResult($dto::tgApiEntity()->name, $result));

        return new TgApiResponse(
            ok: $response['ok'],
            possibleResultTypes: $returnTypes,
            result: $result,
        );
    }

    private function checkResponse(
        TgApiMethodDTOContract|string $dto,
        array $response,
    ): void {
        if (($response['ok'] ?? false) === true) {
            return;
        }
        $description = $response['description'] ?? 'Unknown error';
        $errorCode = $response['error_code'] ?? null;

        $retryAfter = 0;
        if ($errorCode === 429) {
            if (isset($response['parameters']['retry_after'])) {
                if (is_numeric($response['parameters']['retry_after'])) {
                    $retryAfter = $response['parameters']['retry_after'];
                } else {
                    $this->logger->warning(
                        "TgResponseParser: Unexpected retry_after = "
                        .json_encode($response['parameters']['retry_after'])
                    );
                }
            } elseif (preg_match('/retry after (\d+)/i', $description, $matches)) {
                $retryAfter = (int)$matches[1];
            }

            throw new TgFloodWaitException((int)$retryAfter, $description, $errorCode);
        }

        if ($errorCode === 400) {
            throw new TgBadRequestException($description, $errorCode);
        }

        throw new TgApiException($description, $errorCode);
    }

    public function buildInternal(
        string|TgApiMethodDTOContract $dto,
        array|string $expectType,
        mixed $returnLevel,
    ): mixed {
        if (is_array($expectType)) {
            if (
                !is_array($returnLevel)
                || is_string(array_key_first($returnLevel))
            ) {
                throw new TgUnexpectedDataFormatException(
                    $dto::tgApiEntity()->name,
                    $expectType,
                    $returnLevel,
                );
            }
            if (count($expectType) !== 1) {
                throw new TgBotTechnicalWithEntityException(
                    $dto::tgApiEntity()->name,
                    '@todo NotImplemented: Return Types contain not 1 type: '
                    .json_encode($expectType),
                );
            }
            $expectType = array_first($expectType);

            $result = [];
            foreach ($returnLevel as $key => $value) {
                $result[$key] = $this->buildInternal(
                    dto: $dto,
                    expectType: $expectType,
                    returnLevel: $value,
                );
            }

            return $result;
        }
        if (
            str_starts_with($expectType, '\\')
            || str_ends_with($expectType, 'DTO')
        ) {
            if (!is_array($returnLevel)) {
                throw new TgUnexpectedDataFormatException(
                    $dto::tgApiEntity()->name,
                    $expectType,
                    $returnLevel,
                );
            }

            return $this->tgApiDTOMapper->fromArray($expectType, $returnLevel);
        }

        switch ($expectType) {
            case 'null':
                //OR expected only, but not implemented
                throw new TgUnexpectedDataFormatException(
                    $dto::tgApiEntity()->name,
                    $expectType,
                    $returnLevel,
                );
            case 'mixed':
                return $returnLevel;
            case 'bool':
                if (!is_bool($returnLevel)) {
                    throw new TgUnexpectedDataFormatException(
                        $dto::tgApiEntity()->name,
                        $expectType,
                        $returnLevel,
                    );
                }

                return $returnLevel;
            case 'string':
                if (!is_string($returnLevel) && !is_numeric($returnLevel)) {
                    throw new TgUnexpectedDataFormatException(
                        $dto::tgApiEntity()->name,
                        $expectType,
                        $returnLevel,
                    );
                }

                return $returnLevel;
            case 'int':
                if (!is_numeric($returnLevel)) {
                    throw new TgUnexpectedDataFormatException(
                        $dto::tgApiEntity()->name,
                        $expectType,
                        $returnLevel,
                    );
                }
                return $returnLevel;
        }

        throw new TgBotTechnicalWithEntityException(
            $dto::tgApiEntity()->name,
            "@todo NotImplemented: Unsupported Tg Api Return type: $expectType"
            .json_encode($returnLevel),
        );
    }

    private function checkReturnResult(string $entityName, mixed $result): bool
    {
        $unexpected = [];
        if (is_array($result)) {
            foreach ($result as $item) {
                if (!$item instanceof TgApiTypeDTOContract) {
                    $unexpected[] = (is_object($result) ? $result::class : gettype($result)).'[]';
                }
            }
        } elseif (!($result instanceof TgApiTypeDTOContract || is_bool($result))) {
            $unexpected[] = is_object($result) ? $result::class : gettype($result);
        }

        if ($unexpected) {
            $this->logger->warning(
                "Need to update PHPDoc of TgApiResponse: result of $entityName can contain: "
                .json_encode(array_unique($unexpected))
            );

            return false;
        }

        return true;
    }
}
