<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing\ProcessingDispatchers;

use BAGArt\AsyncKernel\Contracts\Daemons\ASKTickableContract;
use BAGArt\AsyncKernel\Wrappers\ASKLogWrapper;
use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\Contracts\Processing\ProcessingDispatcherContract;
use BAGArt\TelegramBot\Contracts\Processing\Processors\TgTypeDTOProcessorContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\Exceptions\TgAsyncException;
use BAGArt\TelegramBot\TgBotSetup;

/**
 * Fork-based dispatcher using pcntl_wait(WNOHANG) for non-blocking child reaping.
 *
 * Unlike {@see PcntlProcessingDispatcher}, this dispatcher is STATEFUL and
 * tick-driven: {@see dispatch()} is fire-and-forget — it forks children up to
 * {@see $maxProcesses} and returns immediately. Child reaping happens in
 * {@see tick()}, which is driven by the kernel's tick loop (register this
 * dispatcher via addTickable() / WithASKTickableContract).
 *
 * Therefore this dispatcher MUST be registered as an {@see ASKTickableContract}
 * in the AsyncKernel — otherwise forked children are never reaped.
 */
class PcntlGrokProcessingDispatcher implements ProcessingDispatcherContract, ASKTickableContract
{
    public const string TYPE = 'pcntl+';

    public int $maxProcesses = 10;

    /**
     * Pending fork jobs waiting for a free process slot.
     *
     * @var list<array{processor: class-string, serviceConfig: TgServiceConfig, botConfig: TgBotConfig, dto: TgApiTypeDTOContract, action: ?string, updateDto: ?TgApiTypeDTOContract}>
     */
    private array $pending = [];

    /** @var array<int, string> child pid => processor class-string */
    private array $pids = [];

    private bool $handlersRegistered = false;

    public function __construct(
        private readonly ?ASKLogWrapper $logger = null,
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

        $this->registerSignalHandlersOnce();

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

        // Eagerly fork as many as fit the current capacity; the rest wait
        // for tick() to reap finished children and free slots.
        $this->forkPending();

        return $dispatched;
    }

    /**
     * Reap finished children and fork any pending jobs that now fit.
     * Driven by the kernel's tick loop. Never blocks.
     */
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

    /**
     * Non-blocking reap of any finished children. Returns true if at least
     * one child was reaped.
     */
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
                $this->logChildExit($pid, $status);
                unset($this->pids[$pid]);
                $reaped = true;
            } else {
                // 0 = no child exited yet; -1 = error / no children. Either
                // way, stop spinning — tick() will be called again.
                break;
            }
        }

        return $reaped;
    }

    /**
     * Fork pending jobs while capacity allows. Returns true if any forked.
     */
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
                throw new TgAsyncException(
                    'Cannot fork process for processor: '.$job['processor']
                );
            }

            if ($pid === 0) {
                $this->runProcessorInChild(
                    $job['processor'],
                    $job['serviceConfig'],
                    $job['botConfig'],
                    $job['dto'],
                    $job['action'],
                    $job['updateDto'],
                );
                exit(0);
            }

            $this->pids[$pid] = $job['processor'];
            $forked = true;
        }

        return $forked;
    }

    /**
     * @param  class-string<TgTypeDTOProcessorContract>  $processorClassName
     */
    private function runProcessorInChild(
        string $processorClassName,
        TgServiceConfig $serviceConfig,
        TgBotConfig $botConfig,
        TgApiTypeDTOContract $dto,
        ?string $action,
        ?TgApiTypeDTOContract $updateDto = null,
    ): void {
        try {
            $processorClassName::build(
                serviceConfig: $serviceConfig,
                botSetup: $this->botSetup,
            )
                ->process($dto, $botConfig, $action, $updateDto);
        } catch (\Throwable) {
            // silently ignore child process failures (matches prior behavior)
        }
    }

    private function logChildExit(int $pid, mixed $status): void
    {
        if ($this->logger === null || $status === null) {
            return;
        }

        if (pcntl_wifexited($status)) {
            $exitCode = pcntl_wexitstatus($status);
            if ($exitCode !== 0) {
                $this->logger->error(__CLASS__." child {$pid} exit with code {$exitCode}");
            }
        } elseif (pcntl_wifsignaled($status)) {
            $signal = pcntl_wtermsig($status);
            $this->logger->warning(__CLASS__." child {$pid} terminated by signal {$signal}");
        }
    }

    /**
     * Signal handler for graceful shutdown: drains remaining children.
     *
     * NOTE: this runs in signal-handler context, NOT in the kernel tick loop.
     * ASK::sleep / Fiber-suspend is not applicable here — signal handlers
     * must be reentrant and cannot yield. The usleep below is intentional
     * and cannot be replaced by the cooperative timer without a signal-safe
     * redesign. This is the only remaining usleep in the dispatcher.
     */
    private function registerSignalHandlersOnce(): void
    {
        if ($this->handlersRegistered) {
            return;
        }
        $this->handlersRegistered = true;

        pcntl_async_signals(true);
        $handler = function (int $signal): void {
            $this->logger?->warning("[PCNTL] Received signal {$signal}, waiting for children to finish...");
            while (!empty($this->pids)) {
                $status = null;
                $pid = pcntl_wait($status, WNOHANG);

                if ($pid > 0) {
                    unset($this->pids[$pid]);
                } else {
                    // signal-handler context — see PHPDoc above.
                    usleep(10_000);
                }
            }

            exit(0);
        };

        pcntl_signal(SIGTERM, $handler);
        pcntl_signal(SIGINT, $handler);
    }
}
