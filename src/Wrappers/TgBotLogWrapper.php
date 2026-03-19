<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Wrappers;

use Psr\Log\LoggerInterface;
use RuntimeException;
use Stringable;

final class TgBotLogWrapper implements LoggerInterface
{
    public static ?LoggerInterface $initLogger = null;

    private readonly LoggerInterface $logger;

    public function __construct(
        ?LoggerInterface $logger = null,
    ) {
        if ($logger !== null) {
            $this->logger = $logger;
            if (self::$initLogger === null) {
                self::$initLogger = $logger;
            }
        } elseif (self::$initLogger !== null) {
            $this->logger = self::$initLogger;
        } else {
            throw new RuntimeException('TgBotLogWrapper: CACHE not injected. Provide Logger first.');
        }
    }

    public static function init(LoggerInterface $logger): void
    {
        self::$initLogger = $logger;
    }

    public function log($level, string|Stringable $message, array $context = []): void
    {
        $this->logger->log($level, $message, $context);
    }

    public function emergency(string|Stringable $message, array $context = []): void
    {
        $this->logger->emergency('[!!!!!] '.$message, $context);
    }

    public function alert(string|Stringable $message, array $context = []): void
    {
        $this->logger->alert('[!!] '.$message, $context);
    }

    public function critical(string|Stringable $message, array $context = []): void
    {
        $this->logger->critical('[!!!!] '.$message, $context);
    }

    public function error(string|Stringable $message, array $context = []): void
    {
        $this->logger->error('[!!!] '.$message, $context);
    }

    public function warning(string|Stringable $message, array $context = []): void
    {
        $this->logger->warning('[!] '.$message, $context);
    }

    public function notice(string|Stringable $message, array $context = []): void
    {
        $this->logger->notice($message, $context);
    }

    public function info(string|Stringable $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    public function debug(string|Stringable $message, array $context = []): void
    {
        $this->logger->debug('[DBG] '.$message, $context);
    }
}
