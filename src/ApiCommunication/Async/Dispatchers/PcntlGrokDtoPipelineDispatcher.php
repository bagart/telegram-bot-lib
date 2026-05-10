<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Async\Dispatchers;

use BAGArt\TelegramBot\Contracts\ApiCommunication\Async\DtoPipelineDispatcherContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\Contracts\TgUpdateProcessor\TgTypeDTOProcessorContract;
use BAGArt\TelegramBot\Exceptions\TgAsyncException;
use BAGArt\TelegramBot\TgUpdateConfig;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;

class PcntlGrokDtoPipelineDispatcher implements DtoPipelineDispatcherContract
{
    public const string TYPE = 'pcntl+';

    public int $maxProcesses = 10;

    /** @var array<int, string> */
    private array $pids = [];

    private readonly TgBotLogWrapper $logger;

    public function __construct(
        ?TgBotLogWrapper $logger = null,
    ) {
        $this->logger = $logger ?? TgBotLogWrapper::build();
    }

    public function dispatch(
        TgUpdateConfig $config,
        TgApiTypeDTOContract $dto,
        string $botId,
        array $processors,
        ?string $action = null,
    ): int {
        if (empty($processors)) {
            return 0;
        }
        $i = 0;
        $activeProcesses = 0;
        $this->pids = [];

        $this->registerSignalHandlers();

        foreach ($processors as $processorClassName) {
            ++$i;
            while ($activeProcesses >= $this->maxProcesses) {
                $this->waitForAnyChild($activeProcesses);
            }

            $pid = pcntl_fork();

            if ($pid === -1) {
                throw new TgAsyncException("Cannot fork process for processor: $processorClassName");
            }

            if ($pid === 0) {
                $this->runProcessorInChild(
                    $processorClassName,
                    $config,
                    $dto,
                    $botId,
                    $action,
                );
                exit(0);
            }

            $this->pids[$pid] = $processorClassName;
            $activeProcesses++;
        }

        while ($activeProcesses > 0) {
            $this->waitForAnyChild($activeProcesses);
        }

        return $i;
    }

    /**
     * @param  list<TgTypeDTOProcessorContract|class-string<TgTypeDTOProcessorContract>>  $processorClassName
     */
    private function runProcessorInChild(
        string $processorClassName,
        TgUpdateConfig $config,
        TgApiTypeDTOContract $dto,
        string $botId,
        ?string $action,
    ): void {
        try {
            /** @var TgTypeDTOProcessorContract $processorClassName */
            $processorClassName::build($config)
                ->process($dto, $botId, $config, $action);
        } catch (\Throwable $e) {
            $logger = TgBotLogWrapper::build();

            $logger->error(
                sprintf(
                    '[PCNTL] Processor failed [%s]: %s in %s:%d',
                    $processorClassName,
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine()
                ),
                [
                    'exception' => get_class($e),
                    'bot_id' => $botId,
                    'action' => $action,
                    'processor' => $processorClassName,
                ]
            );
        }
    }

    private function waitForAnyChild(int &$activeProcesses): void
    {
        $status = null;
        $pid = pcntl_wait($status, WNOHANG);
        assert(is_int($status));

        if ($pid > 0) {
            $activeProcesses--;
            unset($this->pids[$pid]);

            if (pcntl_wifexited($status)) {
                $exitCode = pcntl_wexitstatus($status);
                if ($exitCode !== 0) {
                    $this->logger->error(__CLASS__.' exit with error');
                }
            } elseif (pcntl_wifsignaled($status)) {
                $signal = pcntl_wtermsig($status);
            }
        } elseif ($pid === 0) {
            usleep(10_000);
        } else {
            usleep(5_000);
        }
    }

    private function registerSignalHandlers(): void
    {
        pcntl_async_signals(true);
        $handler = function (int $signal): void {
            $this->logger->warning("[PCNTL] Received signal $signal, waiting for children to finish...");
            while (!empty($this->pids)) {
                $status = null;
                $pid = pcntl_wait($status, WNOHANG);

                if ($pid > 0) {
                    unset($this->pids[$pid]);
                } else {
                    usleep(10_000);
                }
            }

            exit(0);
        };

        pcntl_signal(SIGTERM, $handler);
        pcntl_signal(SIGINT, $handler);
        // pcntl_signal(SIGHUP, $handler);
    }
}
