<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\BotServices;

use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiDTOClientContract;
use BAGArt\TelegramBot\Exceptions\ApiCommunication\TgApiCommunicationException;
use BAGArt\TelegramBot\Exceptions\TgApiUserBreakException;
use BAGArt\TelegramBot\TgApi\Methods\DTO\GetUpdatesMethodDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\UpdateTypeDTO;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;
use BAGArt\TelegramBot\Wrappers\TgBotOutputWrapper;

class TgLongPoller
{
    private const BAR_INTERVAL = 1;

    private bool $keepRunning = true;
    private int $updateCount = 0;
    private ?object $bar = null;

    public function __construct(
        private readonly TgBotApiDTOClientContract $tgDTOClient,
        private readonly TgBotLogWrapper $logger,
        private readonly TgBotOutputWrapper $output,
        private readonly string $token,
    ) {
    }

    public function run(
        callable $fn,
        array $allowedUpdates = ['message', 'callback_query', 'edited_channel_post'],
        int $fnRetry = 1,
        bool $noAck = false,
        int $timeout = 30,
        int $limit = 100,
        int $offset = 0,
        float|int $delayOnFn = 0,
        bool $showBar = true,
    ): int {
        $this->bar = $showBar && $this->output->hasProgressBar()
            ? $this->output->createProgressBar($limit)
            : null;

        $delayOnErr = 5;
        $lastId = $offset;
        $alreadyWarnAboutFullBuffer = false;

        $this->output->info('Telegram bot started. Press Ctrl+C to stop.');

        try {
            while ($this->keepRunning) {
                try {
                    $getUpdatesResponse = $this->tgDTOClient->request(
                        $this->token,
                        new GetUpdatesMethodDTO(
                            offset: $noAck ? 0 : $lastId,
                            limit: $limit,
                            timeout: $timeout,
                            allowedUpdates: $allowedUpdates,
                        )
                    );
                } catch (TgApiUserBreakException $e) {
                    return 1;
                } catch (TgApiCommunicationException $e) {
                    $msg = 'Tg Api Connection '.$e::class." while LongPolling: {$e->getMessage()}";
                    $this->logger->error($msg, [
                        'exception' => $e::class,
                        'message' => $e->getMessage(),
                    ]);
                    $this->output->error($msg);
                    usleep($delayOnErr * 1000 * 1000);
                    $delayOnErr = min((int)($delayOnErr * 1.2 + 0.5), 30);

                    continue;
                }

                $lastUpdateCount = $this->updateCount;
                foreach ($getUpdatesResponse?->result ?? [] as $update) {
                    if ($update->updateId < $lastId) {
                        continue;
                    }
                    ++$this->updateCount;
                    if (self::BAR_INTERVAL && $this->bar !== null && ($this->updateCount % self::BAR_INTERVAL) === 0) {
                        $this->bar->setMaxSteps($this->updateCount + $limit * 10);
                        $this->bar->advance(self::BAR_INTERVAL);
                    }
                    assert($update instanceof UpdateTypeDTO);

                    if ($fn) {
                        $tries = $fnRetry;
                        while ($tries-- > 0) {
                            try {
                                $this->keepRunning = $fn($update, $this->updateCount)
                                    ?? $this->keepRunning;
                                $tries = 0;
                                if ($delayOnFn > 0) {
                                    usleep((int)($delayOnFn * 1000 * 1000));
                                }
                            } catch (TgApiUserBreakException $e) {
                                return 1;
                            } catch (TgApiCommunicationException $e) {
                                $msg = 'Tg Api Connection '.$e::class
                                    ." while run Response Reaction: {$e->getMessage()}";
                                $this->logger->warning($msg, [
                                    'exception' => $e::class,
                                    'message' => $e->getMessage(),
                                    'trace' => "{$e->getFile()}:{$e->getLine()}\n{$e->getTraceAsString()}",
                                ]);
                                $this->output->error($msg);
                                usleep($delayOnErr * 1000 * 1000);
                                $delayOnErr = min((int)($delayOnErr * 1.2 + 0.5), 30);
                            } catch (\Throwable $e) {
                                if ($noAck) {
                                    throw $e;
                                }
                                $msg = 'Tg Response Reaction '.$e::class." : {$e->getMessage()}";
                                $this->logger->error($msg, [
                                    'exception' => $e::class,
                                    'message' => $e->getMessage(),
                                    'trace' => "{$e->getFile()}:{$e->getLine()}\n{$e->getTraceAsString()}",
                                ]);
                                $this->output->error($msg);
                                usleep($delayOnErr * 1000 * 1000);
                                $delayOnErr = min((int)($delayOnErr * 1.2 + 0.5), 30);
                            }
                        }
                    }

                    $lastId = max($lastId, $update->updateId + 1);
                }

                if (
                    $noAck
                    && count($getUpdatesResponse?->result ?? []) >= $limit
                    && $lastUpdateCount === $this->updateCount
                ) {
                    if (!$alreadyWarnAboutFullBuffer) {
                        $this->output->warn('Buffer is full (New messages will delivery after ack queue).');
                        $alreadyWarnAboutFullBuffer = true;
                    }
                } else {
                    $alreadyWarnAboutFullBuffer = false;
                }

                if ($noAck) {
                    usleep((int)(2 * 1000 * 1000));
                } else {
                    $delayOnErr = max((int)($delayOnErr * 0.9), 5);
                    usleep((int)(0.5 * 1000 * 1000));
                }
            }

            return 0;
        } catch (TgApiUserBreakException $e) {
            return 1;
        }
    }

    public function stop(): void
    {
        $this->keepRunning = false;
    }

    public function isRunning(): bool
    {
        return $this->keepRunning;
    }
}
