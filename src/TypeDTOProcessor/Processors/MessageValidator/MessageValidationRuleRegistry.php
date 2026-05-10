<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TypeDTOProcessor\Processors\MessageValidator;

use Generator;

class MessageValidationRuleRegistry
{
    /** @var array<MessageValidationRule> */
    private array $rules = [];

    public function register(MessageValidationRule $rule): void
    {
        $this->rules[] = $rule;
        usort(
            $this->rules,
            fn (MessageValidationRule $a, MessageValidationRule $b) => $b->priority() <=> $a->priority()
        );
    }

    /** @param  array<MessageValidationRule>  $rules */
    public function registerMany(array $rules): void
    {
        foreach ($rules as $rule) {
            $this->rules[] = $rule;
        }
        usort(
            $this->rules,
            fn (MessageValidationRule $a, MessageValidationRule $b) => $b->priority() <=> $a->priority()
        );
    }

    public function rules(): Generator
    {
        foreach ($this->rules as $rule) {
            yield $rule;
        }
    }
}
