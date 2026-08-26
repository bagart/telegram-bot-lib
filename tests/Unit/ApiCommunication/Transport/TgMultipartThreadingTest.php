<?php

declare(strict_types=1);

use BAGArt\ASKClient\Contracts\Pipeline\ASKFutureContract;
use BAGArt\ASKClient\Contracts\Transport\HttpTransportContract;
use BAGArt\ASKClient\Dto\ASKHttpRequest;
use BAGArt\ASKClient\Dto\ASKHttpResponse;
use BAGArt\AsyncKernel\Contracts\ASKPromiseContract;
use BAGArt\AsyncKernel\Promise\ASKPromise;
use BAGArt\TelegramBot\ApiCommunication\Clients\TgBotApiDTOClient;
use BAGArt\TelegramBot\ApiCommunication\ClientServices\TgRequestFactory;
use BAGArt\TelegramBot\ApiCommunication\Transports\TgBotApiTransport;
use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiClientContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiTransportContract;
use BAGArt\TelegramBot\Http\Pure\TgResponseParser;
use BAGArt\TelegramBot\TgApi\Methods\DTO\SendVoiceMethodDTO;
use BAGArt\TelegramBot\TgApiServices\TgApiDTOMapper;
use BAGArt\TelegramBot\TgApiServices\TgEntityToDTORegistry;

/*
 * Track B threading (todo.tts.md §6): file:// values on input-file typed
 * fields split out of the JSON body into ASKHttpRequest::$files at the send
 * point, while toArray() keeps them verbatim for queue-safe serialization.
 */

function tbCapturingTransport(): HttpTransportContract
{
    return new class () implements HttpTransportContract {
        public ?ASKHttpRequest $last = null;

        public function request(ASKHttpRequest $request): ASKHttpResponse
        {
            $this->last = $request;

            return ASKHttpResponse::fromJson(tbOkResponse());
        }

        public function requestAsync(ASKHttpRequest $request): ASKPromiseContract
        {
            $this->last = $request;

            return ASKPromise::resolved(ASKHttpResponse::fromJson(tbOkResponse()));
        }
    };
}

function tbMapper(): TgApiDTOMapper
{
    return new TgApiDTOMapper(TgEntityToDTORegistry::build());
}

function tbOkResponse(): array
{
    return ['ok' => true, 'result' => [
        'message_id' => 1,
        'date' => time(),
        'chat' => ['id' => 7, 'type' => 'private'],
    ]];
}

describe('Track B multipart threading', function () {
    it('keeps file:// values verbatim in toArray() for queue safety', function () {
        $dto = new SendVoiceMethodDTO(chatId: '7', voice: 'file:///tmp/tts.ogg', caption: 'cap');

        expect(tbMapper()->toArray($dto))->toHaveKey('voice', 'file:///tmp/tts.ogg');
    });

    it('splits file:// voice fields out of parameters at send time', function () {
        $dto = new SendVoiceMethodDTO(chatId: '7', voice: 'file:///tmp/tts.ogg', caption: 'cap');

        $split = tbMapper()->splitRequest($dto);

        expect($split['parameters'])->not->toHaveKey('voice')
            ->and($split['parameters'])->toHaveKey('chat_id', '7')
            ->and($split['files'])->toBe(['voice' => '/tmp/tts.ogg']);
    });

    it('leaves file_ids and URLs on input-file fields inside JSON', function () {
        $fileId = new SendVoiceMethodDTO(chatId: '7', voice: 'AgAC-file-id');
        $url = new SendVoiceMethodDTO(chatId: '7', voice: 'https://cdn.example.org/x.ogg');

        expect(tbMapper()->splitRequest($fileId)['files'])->toBe([])
            ->and(tbMapper()->splitRequest($fileId)['parameters']['voice'])->toBe('AgAC-file-id')
            ->and(tbMapper()->splitRequest($url)['files'])->toBe([])
            ->and(tbMapper()->splitRequest($url)['parameters']['voice'])->toBe('https://cdn.example.org/x.ogg');
    });

    it('threads files from the factory into the ASK request', function () {
        $factory = new TgRequestFactory();

        $request = $factory->make(
            tgMethodName: 'sendVoice',
            parameters: ['chat_id' => '7'],
            botConfig: new TgBotConfig(token: '123:test', botId: 'test_bot'),
            timeout: 8,
            files: ['voice' => '/tmp/tts.ogg'],
        );

        expect($request->files)->toBe(['voice' => '/tmp/tts.ogg'])
            ->and($request->body)->toBeNull()
            ->and($request->queryParams)->toBe(['chat_id' => '7']);
    });

    it('delivers files through the transport to ASKHttpRequest', function () {
        $transport = tbCapturingTransport();
        $tgTransport = new TgBotApiTransport($transport);

        $tgTransport->requestAsync(
            config: new TgBotConfig(token: '123:test', botId: 'test_bot'),
            method: 'sendVoice',
            params: ['chat_id' => '7'],
            timeout: 8,
            files: ['voice' => '/tmp/tts.ogg'],
        )->await();

        expect($transport->last)->toBeInstanceOf(ASKHttpRequest::class)
            ->and($transport->last->files)->toBe(['voice' => '/tmp/tts.ogg'])
            ->and($transport->last->body)->toBeNull()
            ->and($transport->last->getUrlWithQuery())->toContain('/sendVoice');
    });

    it('splits and threads automatically inside the DTO client', function () {
        $transport = tbCapturingTransport();
        $tgTransport = new TgBotApiTransport($transport);

        $client = new TgBotApiDTOClient(
            new class ($tgTransport) implements TgBotApiClientContract {
                public function __construct(
                    private readonly TgBotApiTransportContract $inner,
                ) {
                }

                public function request(TgBotConfig $config, string $method, array $params = [], array $files = []): array
                {
                    return $this->inner->request($config, $method, $params, null, $files);
                }

                public function requestAsync(TgBotConfig $config, string $method, array $params = [], ?int $timeout = null, array $files = []): ASKFutureContract
                {
                    return $this->inner->requestAsync($config, $method, $params, $timeout, $files);
                }
            },
            tbMapper(),
            new TgResponseParser(tbMapper()),
        );

        $response = $client->request(
            botConfig: new TgBotConfig(token: '123:test', botId: 'test_bot'),
            dto: new SendVoiceMethodDTO(chatId: '7', voice: 'file:///tmp/tts.ogg'),
            timeout: 8,
        );

        expect($response->ok)->toBeTrue()
            ->and($transport->last->files)->toBe(['voice' => '/tmp/tts.ogg'])
            ->and($transport->last->body)->toBeNull();
    });
});
