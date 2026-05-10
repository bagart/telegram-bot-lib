<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TypeDTOProcessor\Processors\MessageValidator;

use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;

class RegexValidationRule implements MessageValidationRule
{
    /** @var array<string> */
    private readonly array $patterns;

    /**
     * @param  array<string>  $patterns  Regex patterns to match against message text
     * @param  int  $rulePriority  Priority of this rule (higher = checked first)
     * @param  MessageVerdictActionEnum  $action  Action to take when rule matches
     * @param  int|null  $restrictDuration  Restriction duration in seconds (null = forever)
     */
    public function __construct(
        array $patterns,
        private readonly int $rulePriority = 10,
        private readonly MessageVerdictActionEnum $action = MessageVerdictActionEnum::Restrict,
        private readonly ?int $restrictDuration = null,
    ) {
        $this->patterns = array_values(array_filter($patterns, fn (string $p) => @preg_match($p, '') !== false));
    }

    public function priority(): int
    {
        return $this->rulePriority;
    }

    public function validate(MessageTypeDTO $dto): ?MessageValidationVerdict
    {
        $text = $this->extractText($dto);
        if ($text === null) {
            return null;
        }

        foreach ($this->patterns as $pattern) {
            if (@preg_match($pattern, $text) === 1) {
                return MessageValidationVerdict::reject(
                    action: $this->action,
                    reason: 'спам',
                    matchedRule: $pattern,
                    priority: $this->rulePriority,
                    restrictDuration: $this->restrictDuration,
                );
            }
        }

        return null;
    }

    private function extractText(MessageTypeDTO $dto): ?string
    {
        $parts = [];

        if ($dto->text !== null) {
            $parts[] = $dto->text;
        }

        if ($dto->caption !== null) {
            $parts[] = $dto->caption;
        }

        $combined = implode("\n", $parts);

        return $combined !== '' ? $combined : null;
    }
}
