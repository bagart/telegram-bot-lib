<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Wrappers;

use Symfony\Component\Console\Helper\ProgressBar;

class TgBotOutputWrapper
{
    private readonly bool $symfony;
    private readonly mixed $output;

    /**
     * @param  resource|object  $output  Symfony OutputInterface or a PHP stream resource
     */
    public function __construct(
        mixed $output = null,
    ) {
        $this->output = $output ?? \STDOUT;
        $this->symfony = is_object($this->output) && method_exists($this->output, 'writeln');
    }

    public function info(string $message): void
    {
        if ($this->symfony) {
            $this->output->writeln("<info>{$message}</info>");
        } else {
            $this->writeln($message);
        }
    }

    private function writeln(string $message): void
    {
        if (is_resource($this->output)) {
            fwrite($this->output, $message."\n");
        }
    }

    public function error(string $message): void
    {
        if ($this->symfony) {
            $this->output->writeln("<error>{$message}</error>");
        } else {
            $this->writeln("ERROR: {$message}");
        }
    }

    public function warn(string $message): void
    {
        if ($this->symfony) {
            $this->output->writeln("<comment>{$message}</comment>");
        } else {
            $this->writeln("WARN: {$message}");
        }
    }

    public function line(string $message = ''): void
    {
        if ($this->symfony) {
            $this->output->writeln($message);
        } else {
            $this->writeln($message);
        }
    }

    public function newLine(): void
    {
        if ($this->symfony) {
            $this->output->newLine();
        } else {
            $this->writeln('');
        }
    }

    /**
     * @return object|null  Symfony ProgressBar or null if unavailable
     */
    public function createProgressBar(int $max): ?ProgressBar
    {
        if (!$this->hasProgressBar()) {
            return null;
        }

        return new ProgressBar($this->output, $max);
    }

    public function hasProgressBar(): bool
    {
        if (!$this->symfony) {
            return false;
        }

        return class_exists(ProgressBar::class);
    }
}
