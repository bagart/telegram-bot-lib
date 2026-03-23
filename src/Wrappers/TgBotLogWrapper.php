<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Wrappers;

use Psr\Log\LoggerInterface;
use RuntimeException;
use Stringable;

final class TgBotLogWrapper implements LoggerInterface
{
    public static ?LoggerInterface $initLogger = null;
    public static bool $initDebugEnabled = false;

    public static function build(): self
    {
        if (self::$initLogger === null) {
            throw new RuntimeException('TgBotLogWrapper::build() called without initLogger. Call TgBotLogWrapper::init() first.');
        }

        return new self(static::$initLogger, static::$initDebugEnabled);
    }

    public function __construct(
        private readonly LoggerInterface $logger,
        public bool $debugEnabled = false,
    ) {
        self::$initLogger ??= $logger;
    }

    public static function init(LoggerInterface $logger, bool $debugEnabled = false): void
    {
        self::$initLogger = $logger;
        static::$initDebugEnabled = $debugEnabled;
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
        if ($this->debugEnabled) {
            $this->logger->debug('[DBG] '.$message, $context);
        }
    }
}
