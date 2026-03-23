<?php

declare(strict_types=1);

use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

describe('TgApiProperty', function () {
    describe('constructor', function () {
        it('creates property with all fields', function () {
            $property = new TgApiProperty(
                property: 'firstName',
                tgPropName: 'first_name',
                types: ['string'],
                tgTypes: ['String'],
                nullable: false,
                required: true,
            );

            expect($property->property)->toBe('firstName')
                ->and($property->tgPropName)->toBe('first_name')
                ->and($property->types)->toBe(['string'])
                ->and($property->tgTypes)->toBe(['String'])
                ->and($property->nullable)->toBeFalse()
                ->and($property->required)->toBeTrue();
        });

        it('creates property with nullable field', function () {
            $property = new TgApiProperty(
                property: 'lastName',
                tgPropName: 'last_name',
                types: ['string', 'null'],
                tgTypes: ['String'],
                nullable: true,
                required: false,
            );

            expect($property->nullable)->toBeTrue()
                ->and($property->required)->toBeFalse();
        });

        it('creates property with DTO types', function () {
            $property = new TgApiProperty(
                property: 'chat',
                tgPropName: 'chat',
                types: ['\BAGArt\TelegramBot\TgApi\Types\DTO\ChatTypeDTO'],
                tgTypes: ['Chat'],
                nullable: false,
                required: true,
            );

            expect($property->types)->toContain('\BAGArt\TelegramBot\TgApi\Types\DTO\ChatTypeDTO');
        });

        it('creates property with array types', function () {
            $property = new TgApiProperty(
                property: 'entities',
                tgPropName: 'entities',
                types: [['\BAGArt\TelegramBot\TgApi\Types\DTO\MessageEntityTypeDTO']],
                tgTypes: ['Array<MessageEntity>'],
                nullable: false,
                required: false,
            );

            expect($property->types)->toBeArray()
                ->and($property->types[0])->toBeArray();
        });
    });
});
