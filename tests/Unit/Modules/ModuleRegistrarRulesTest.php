<?php

declare(strict_types=1);

use BAGArt\TelegramBot\Processing\Processors\MessageValidator\AdvertisingValidationRule;
use BAGArt\TelegramBot\Processing\Processors\MessageValidator\MessageValidationRule;
use BAGArt\TelegramBot\Processing\Processors\MessageValidator\MessageValidationRuleRegistry;
use BAGArt\TelegramBot\Processing\Processors\MessageValidator\MessageValidationVerdict;
use BAGArt\TelegramBot\Processing\Processors\MessageValidator\WeightedMessageValidationRule;
use BAGArt\TelegramBot\Modules\TypedModuleRegistrar;
use BAGArt\TelegramBot\Processing\TypeDTOProcessorRegistry;
use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;

final class ModulesRulesLowPriorityFixture implements MessageValidationRule
{
    public function priority(): int
    {
        return 5;
    }

    public function validate(MessageTypeDTO $dto): ?MessageValidationVerdict
    {
        return null;
    }
}

final class ModulesRulesHighPriorityFixture implements MessageValidationRule
{
    public function priority(): int
    {
        return 50;
    }

    public function validate(MessageTypeDTO $dto): ?MessageValidationVerdict
    {
        return null;
    }
}

function modulesRulesRegistrar(?MessageValidationRuleRegistry $rules): TypedModuleRegistrar
{
    return new TypedModuleRegistrar(
        TypeDTOProcessorRegistry::build(),
        $rules,
    );
}

describe('validationRule registration', function () {
    it('registers a rule class into the shared rule registry', function () {
        $rules = new MessageValidationRuleRegistry();

        modulesRulesRegistrar($rules)->validationRule(ModulesRulesLowPriorityFixture::class);

        $classes = array_map(static fn ($r) => $r::class, iterator_to_array($rules->rules()));
        expect($classes)->toContain(ModulesRulesLowPriorityFixture::class);
    });

    it('applies the weight as a priority override', function () {
        $rules = new MessageValidationRuleRegistry();
        $rules->registerClass(ModulesRulesLowPriorityFixture::class, weight: 90);

        $registered = iterator_to_array($rules->rules());
        expect($registered[0])->toBeInstanceOf(WeightedMessageValidationRule::class);
        expect($registered[0]->priority())->toBe(90);
    });

    it('keeps the rule own priority when weight is zero', function () {
        $rules = new MessageValidationRuleRegistry();
        $rules->registerClass(ModulesRulesLowPriorityFixture::class);

        expect(iterator_to_array($rules->rules())[0]->priority())->toBe(5);
    });

    it('deduplicates repeated registration of the same rule class (AC-9)', function () {
        $rules = new MessageValidationRuleRegistry();

        $registrar = modulesRulesRegistrar($rules);
        $registrar->validationRule(ModulesRulesLowPriorityFixture::class);
        $registrar->validationRule(ModulesRulesLowPriorityFixture::class);

        expect(iterator_to_array($rules->rules()))->toHaveCount(1);
    });

    it('sorts rules by priority descending', function () {
        $rules = new MessageValidationRuleRegistry();
        $rules->registerClass(ModulesRulesLowPriorityFixture::class);
        $rules->registerClass(ModulesRulesHighPriorityFixture::class);

        $priorities = array_map(static fn ($r) => $r->priority(), iterator_to_array($rules->rules()));
        expect($priorities)->toBe([50, 5]);
    });

    it('withCoreRules() registers the AdvertisingValidationRule by default', function () {
        $classes = array_map(
            static fn ($r) => $r::class,
            iterator_to_array(MessageValidationRuleRegistry::withCoreRules()->rules()),
        );

        expect($classes)->toContain(AdvertisingValidationRule::class);
    });

    it('throws when no rule registry is bound to the registrar', function () {
        expect(fn () => modulesRulesRegistrar(null)->validationRule(ModulesRulesLowPriorityFixture::class))
            ->toThrow(LogicException::class);
    });
});
