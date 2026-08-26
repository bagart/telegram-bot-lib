<?php

declare(strict_types=1);

use BAGArt\TelegramBot\Modules\TypedModuleRegistrar;
use BAGArt\TelegramBot\Processing\TypeDTOProcessorRegistry;

describe('TypedModuleRegistrar validation unification', function () {
    it('rejects command() contract mismatch with LogicException (assertion-independent)', function () {
        $registrar = new TypedModuleRegistrar(TypeDTOProcessorRegistry::build());

        $registrar->command('ping', DateTime::class);
    })->throws(LogicException::class);

    it('rejects registerAttributed() for a non-existent class with LogicException', function () {
        $registrar = new TypedModuleRegistrar(TypeDTOProcessorRegistry::build());

        $registrar->registerAttributed('App\\Does\\Not\\Exist');
    })->throws(LogicException::class);

    it('refuses bare web declarations even for existing classes: ownership context is mandatory', function () {
        $registrar = new TypedModuleRegistrar(TypeDTOProcessorRegistry::build());

        $registrar->webUi(DateTime::class);
    })->throws(LogicException::class);

    it('rejects web declarations for non-existent classes with the raw existence error', function () {
        $registrar = new TypedModuleRegistrar(TypeDTOProcessorRegistry::build());

        $registrar->webApi('App\\Does\\Not\\Exist');
    })->throws(LogicException::class, 'App\Does\Not\Exist does not exist');
});
