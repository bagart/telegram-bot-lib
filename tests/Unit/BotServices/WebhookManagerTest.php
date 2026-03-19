<?php

declare(strict_types=1);

use BAGArt\TelegramBot\TgIntegration\WebhookManager;
use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiDTOClientContract;
use BAGArt\TelegramBot\TgApi\Types\DTO\WebhookInfoTypeDTO;

describe('WebhookManager', function () {
    describe('buildTextInfo()', function () {
        it('formats webhook info with all fields', function () {
            $dtoClient = Mockery::mock(TgBotApiDTOClientContract::class);
            $manager = new WebhookManager($dtoClient);

            $info = new WebhookInfoTypeDTO(
                url: 'https://example.com/webhook',
                hasCustomCertificate: false,
                pendingUpdateCount: 5,
                ipAddress: '1.2.3.4',
                maxConnections: 100,
                allowedUpdates: ['message', 'callback_query'],
            );

            $text = $manager->buildTextInfo($info);

            expect($text)->toContain('URL:               https://example.com/webhook')
                ->toContain('Secret:            yes')
                ->toContain('Pending:           5')
                ->toContain('Max connections:   100')
                ->toContain('Allowed updates:   message, callback_query')
                ->toContain('IP address:        1.2.3.4');
        });

        it('formats webhook info with custom certificate', function () {
            $dtoClient = Mockery::mock(TgBotApiDTOClientContract::class);
            $manager = new WebhookManager($dtoClient);

            $info = new WebhookInfoTypeDTO(
                url: 'https://example.com/webhook',
                hasCustomCertificate: true,
                pendingUpdateCount: 0,
            );

            $text = $manager->buildTextInfo($info);

            expect($text)->toContain('Secret:            custom cert');
        });

        it('formats webhook info with no URL', function () {
            $dtoClient = Mockery::mock(TgBotApiDTOClientContract::class);
            $manager = new WebhookManager($dtoClient);

            $info = new WebhookInfoTypeDTO(
                url: '',
                hasCustomCertificate: false,
                pendingUpdateCount: 0,
            );

            $text = $manager->buildTextInfo($info);

            expect($text)->toContain('URL:               (not set)')
                ->toContain('Secret:            -');
        });

        it('formats webhook info with error message', function () {
            $dtoClient = Mockery::mock(TgBotApiDTOClientContract::class);
            $manager = new WebhookManager($dtoClient);

            $info = new WebhookInfoTypeDTO(
                url: 'https://example.com/webhook',
                hasCustomCertificate: false,
                pendingUpdateCount: 0,
                lastErrorMessage: 'Connection refused',
                lastErrorDate: 1700000000,
            );

            $text = $manager->buildTextInfo($info);

            expect($text)->toContain('Error:             Connection refused')
                ->toContain('Error at:');
        });

        it('uses default max connections when null', function () {
            $dtoClient = Mockery::mock(TgBotApiDTOClientContract::class);
            $manager = new WebhookManager($dtoClient);

            $info = new WebhookInfoTypeDTO(
                url: 'https://example.com/webhook',
                hasCustomCertificate: false,
                pendingUpdateCount: 0,
                maxConnections: null,
            );

            $text = $manager->buildTextInfo($info);

            expect($text)->toContain('Max connections:   default (40)');
        });

        it('uses default IP when null', function () {
            $dtoClient = Mockery::mock(TgBotApiDTOClientContract::class);
            $manager = new WebhookManager($dtoClient);

            $info = new WebhookInfoTypeDTO(
                url: 'https://example.com/webhook',
                hasCustomCertificate: false,
                pendingUpdateCount: 0,
                ipAddress: null,
            );

            $text = $manager->buildTextInfo($info);

            expect($text)->toContain('IP address:        default');
        });

        it('uses all for allowed updates when null', function () {
            $dtoClient = Mockery::mock(TgBotApiDTOClientContract::class);
            $manager = new WebhookManager($dtoClient);

            $info = new WebhookInfoTypeDTO(
                url: 'https://example.com/webhook',
                hasCustomCertificate: false,
                pendingUpdateCount: 0,
                allowedUpdates: null,
            );

            $text = $manager->buildTextInfo($info);

            expect($text)->toContain('Allowed updates:   all');
        });
    });
});
