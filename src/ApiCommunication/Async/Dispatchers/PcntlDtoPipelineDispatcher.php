<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Async\Dispatchers;

use BAGArt\TelegramBot\Contracts\ApiCommunication\Async\DtoPipelineDispatcherContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\Contracts\TgUpdateProcessor\TgTypeDTOProcessorContract;
use BAGArt\TelegramBot\Exceptions\TgAsyncException;
use BAGArt\TelegramBot\TgUpdateConfig;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;

class PcntlDtoPipelineDispatcher implements DtoPipelineDispatcherContract
{
    public const string TYPE = 'pcntl';
    public int $maxProcesses = 10;

    /**
     * @param  list<TgTypeDTOProcessorContract|class-string<TgTypeDTOProcessorContract>>  $processors
     */
    public function dispatch(
        TgUpdateConfig $config,
        TgApiTypeDTOContract $dto,
        string $botId,
        array $processors,
        ?string $action = null,
    ): int {
        $activeProcesses = 0;
        $i = 0;

        foreach ($processors as $processorClassName) {
            ++$i;
            while ($activeProcesses >= $this->maxProcesses) {
                $status = null;

                $finishedPid = pcntl_wait($status);

                if ($finishedPid > 0) {
                    $activeProcesses--;
                }
            }

            $pid = pcntl_fork();

            if ($pid === -1) {
                throw new TgAsyncException('Cannot fork process');
            }
            if ($pid === 0) {
                try {
                    $processor = $processorClassName::build($config);
                    $processor->process($dto, $botId, $config, $action);
                } catch (\Throwable $e) {
                    $logger = TgBotLogWrapper::build();
                    $logger->error('PcntlDtoPipelineDispatcher processDto '.$e::class.": {$e->getMessage()}");
                }

                exit(0);
            }

            $activeProcesses++;
        }

        while ($activeProcesses > 0) {
            $status = null;

            $finishedPid = pcntl_wait($status);

            if ($finishedPid > 0) {
                $activeProcesses--;
            }
        }

        return $i;
    }
}
