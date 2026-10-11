<?php

declare(strict_types=1);

use BAGArt\TelegramBotAntispam\Processors\AntispamReportCommand;
use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\Contracts\Modules\CommandRouteContract;
use BAGArt\TelegramBot\Modules\TgCommandRegistry;
use BAGArt\TelegramBot\Processing\RegisteredUpdateProcessorSelector;
use BAGArt\TelegramBot\TgApi\Types\DTO\ChatTypeDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\UpdateTypeDTO;
use BAGArt\TelegramBot\TgApi\Types\Enum\ChatPropTypeEnum;
use BAGArt\TelegramBot\TgBotSetupFactory;
use BAGArt\TelegramBotNettools\Commands\CommandMap;
use BAGArt\TelegramBotNettools\Commands\ReportCommand;

/**
 * /report ownership (Q5 option 2, ADR-004): the flat registry is last-wins,
 * so a shared token resolves by deployment order — both modules claiming
 * `report` made the winner nondeterministic. With nettools' claim renamed to
 * `netreport` every token has exactly one owner, and the same holds on the
 * unsynced-route path where an empty bot_module_routes table leaves the flat
 * registry in charge.
 */
beforeEach(function () {
    config('telegram.modules'); // forces the module config scan
});

/** An unsynced/empty route table: every command falls back to the registry. */
function reportOwnershipUnsyncedRoutes(): CommandRouteContract
{
    return new class () implements CommandRouteContract {
        public function processorOf(string $commandName, string $botId): ?string
        {
            return null;
        }
    };
}

/** @return list<class-string> processor classes selected for the message */
function reportOwnershipSelected(string $text): array
{
    $selector = new RegisteredUpdateProcessorSelector(
        serviceConfig: new TgServiceConfig(),
        botSetup: app(TgBotSetupFactory::class)->create(serviceConfig: new TgServiceConfig()),
        commandRoutes: reportOwnershipUnsyncedRoutes(),
    );

    $update = new UpdateTypeDTO(
        updateId: 1,
        message: new MessageTypeDTO(
            messageId: 10,
            date: time(),
            chat: new ChatTypeDTO(id: '100', type: ChatPropTypeEnum::GROUP),
            text: $text,
        ),
    );

    $botConfig = new TgBotConfig(token: 'test:token', botId: 'test_bot');
    $selected = [];
    foreach ($selector->selectProcessors($update, $botConfig) as $batch) {
        // The selector contract's @return types the yielded value as a single
        // processor although the implementation yields a list — normalize first.
        foreach ((array) $batch as $processor) {
            $selected[] = $processor::class;
        }
    }

    return $selected;
}

it('gives each report token exactly one flat-registry owner', function () {
    $registry = app(TgCommandRegistry::class);

    expect($registry->processorOf('report'))->toBe(AntispamReportCommand::class)
        ->and($registry->processorOf('netreport'))->toBe(ReportCommand::class)
        ->and(AntispamReportCommand::NAME)->toBe('report')
        ->and(ReportCommand::NAME)->toBe('netreport')
        ->and(CommandMap::byName('report'))->toBeNull('nettools must not claim the flat token report')
        ->and(CommandMap::byName('netreport'))->toBe(ReportCommand::class);
});

it('dispatches /report to antispam and /netreport to nettools with an unsynced route table', function () {
    expect(reportOwnershipSelected('/report'))->toBe([AntispamReportCommand::class])
        ->and(reportOwnershipSelected('/netreport'))->toBe([ReportCommand::class]);
});
