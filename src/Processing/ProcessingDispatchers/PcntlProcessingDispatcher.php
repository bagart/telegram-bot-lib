<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing\ProcessingDispatchers;

use BAGArt\AsyncKernel\Contracts\Daemons\ASKTickableContract;
use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\Contracts\Processing\ProcessingDispatcherContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\Exceptions\TgAsyncException;
use BAGArt\TelegramBot\TgBotSetup;

/**
 * Simple fork-based dispatcher.
 *
 * STATEFUL and tick-driven like {@see PcntlGrokProcessingDispatcher}, but
 * without per-child exit logging. {@see dispatch()} is fire-and-forget:
 * it queues processors and forks up to {@see $maxProcesses}; reaping happens
 * in {@see tick()} via non-blocking pcntl_wait(WNOHANG).
 *
 * MUST be registered as an {@see ASKTickableContract} in the AsyncKernel
 * so forked children are reaped.
 */
class PcntlProcessingDispatcher implements ProcessingDispatcherContract, ASKTickableContract
{
    public const string TYPE = 'pcntl';

    public int $maxProcesses = 10;

    /**
     * @var list<array{processor: class-string, serviceConfig: TgServiceConfig, botConfig: TgBotConfig, dto: TgApiTypeDTOContract, action: ?string, updateDto: ?TgApiTypeDTOContract}>
     */
    private array $pending = [];

    /** @var array<int, string> */
    private array $pids = [];

    public function __construct(
        private readonly ?TgBotSetup $botSetup = null,
    ) {
    }

    public function dispatch(
        TgServiceConfig $serviceConfig,
        TgBotConfig $botConfig,
        TgApiTypeDTOContract $dto,
        array $processors,
        ?string $action = null,
        ?TgApiTypeDTOContract $updateDto = null,
    ): int {
        if (empty($processors)) {
            return 0;
        }

        $dispatched = 0;
        foreach ($processors as $processorClassName) {
            $this->pending[] = [
                'processor' => $processorClassName,
                'serviceConfig' => $serviceConfig,
                'botConfig' => $botConfig,
                'dto' => $dto,
                'action' => $action,
                'updateDto' => $updateDto,
            ];
            $dispatched++;
        }

        $this->forkPending();

        return $dispatched;
    }

    public function tick(int $systemPressure): void
    {
        if ($this->pending === [] && $this->pids === []) {
            return;
        }

        $this->reapChildren();
        $this->forkPending();
    }

    public function pressure(): int
    {
        $total = count($this->pending) + count($this->pids);

        if ($total === 0) {
            return 0;
        }

        return (int) round(($total / $this->maxProcesses) * 100);
    }

    public function isIdle(): bool
    {
        return $this->pending === [] && $this->pids === [];
    }

    public function queueSize(): int
    {
        return count($this->pending) + count($this->pids);
    }

    private function reapChildren(): bool
    {
        if ($this->pids === []) {
            return false;
        }

        $reaped = false;
        while (count($this->pids) > 0) {
            $status = null;
            $pid = pcntl_wait($status, WNOHANG);

            if ($pid > 0) {
                unset($this->pids[$pid]);
                $reaped = true;
            } else {
                break;
            }
        }

        return $reaped;
    }

    private function forkPending(): bool
    {
        if ($this->pending === []) {
            return false;
        }

        $forked = false;
        while ($this->pending !== [] && count($this->pids) < $this->maxProcesses) {
            $job = array_shift($this->pending);

            $pid = pcntl_fork();

            if ($pid === -1) {
                throw new TgAsyncException('Cannot fork process');
            }

            if ($pid === 0) {
                try {
                    /** @var class-string $processorClassName */
                    $processorClassName = $job['processor'];
                    $processor = $processorClassName::build($job['serviceConfig'], $this->botSetup);
                    $processor->process(
                        $job['dto'],
                        $job['botConfig'],
                        $job['action'],
                        $job['updateDto'],
                    );
                } catch (\Throwable) {
                    exit(1);
                }

                exit(0);
            }

            $this->pids[$pid] = $job['processor'];
            $forked = true;
        }

        return $forked;
    }
}
