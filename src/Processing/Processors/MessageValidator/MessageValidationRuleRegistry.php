<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing\Processors\MessageValidator;

use Generator;

class MessageValidationRuleRegistry
{
    /** @var array<MessageValidationRule> */
    private array $rules = [];

    /**
     * Registry with the core rule set — the default for setups that do not
     * carry a shared rule registry (pure-PHP mode).
     */
    public static function withCoreRules(): self
    {
        $registry = new self();
        $registry->register(new AdvertisingValidationRule());

        return $registry;
    }

    public function register(MessageValidationRule $rule): void
    {
        foreach ($this->rules as $existing) {
            if ($existing::class === $rule::class) {
                return;
            }
        }

        $this->rules[] = $rule;
        $this->sort();
    }

    /** @param  array<MessageValidationRule>  $rules */
    public function registerMany(array $rules): void
    {
        foreach ($rules as $rule) {
            $this->register($rule);
        }
    }

    /**
     * Registers a rule by class-string with an optional priority weight that
     * overrides the rule's own priority() — the module-registrar entry point.
     *
     * @param  class-string<MessageValidationRule>  $ruleClass
     */
    public function registerClass(string $ruleClass, int $weight = 0): void
    {
        $this->register(new $ruleClass());

        if ($weight !== 0) {
            $this->rules = array_map(
                fn (MessageValidationRule $rule): MessageValidationRule => $rule::class === $ruleClass
                    ? new WeightedMessageValidationRule($rule, $weight)
                    : $rule,
                $this->rules,
            );
            $this->sort();
        }
    }

    /** @return Generator<MessageValidationRule> */
    public function rules(): Generator
    {
        foreach ($this->rules as $rule) {
            yield $rule;
        }
    }

    private function sort(): void
    {
        usort(
            $this->rules,
            fn (MessageValidationRule $a, MessageValidationRule $b) => $b->priority() <=> $a->priority()
        );
    }
}
