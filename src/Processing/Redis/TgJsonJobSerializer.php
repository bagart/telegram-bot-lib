<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing\Redis;

use BAGArt\ASKClient\Contracts\Queue\JobSerializerContract;
use BAGArt\AsyncKernel\Job\AsyncJob;
use BAGArt\TelegramBot\Processing\Update\UpdateContext;

final class TgJsonJobSerializer implements JobSerializerContract
{
    public function serialize(AsyncJob $job): string
    {
        /** @var TgAsyncJob $job */
        return json_encode([
            'jobId' => $job->jobId,
            'partitionKey' => $job->partitionKey,
            'createdAt' => $job->createdAt,
            'attempt' => $job->attempt,
            'retryAt' => $job->retryAt,
            'context' => [
                'dto' => serialize($job->context->dto),
                'processor' => $job->context->processor,
                'botConfig' => serialize($job->context->botConfig),
                'executionKey' => $job->context->executionKey,
                'jobId' => $job->context->jobId,
                'attempt' => $job->context->attempt,
                'source' => $job->context->source,
                'receivedAt' => $job->context->receivedAt,
            ],
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }

    public function deserialize(string $payload): AsyncJob
    {
        $data = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);

        $c = $data['context'];

        $context = new UpdateContext(
            dto: unserialize($c['dto'], ['allowed_classes' => true]),
            processor: $c['processor'],
            botConfig: unserialize($c['botConfig'], ['allowed_classes' => true]),
            executionKey: $c['executionKey'],
            jobId: $c['jobId'],
            attempt: $c['attempt'],
            source: $c['source'],
            receivedAt: $c['receivedAt'],
        );

        return new TgAsyncJob(
            jobId: $data['jobId'],
            partitionKey: $data['partitionKey'],
            context: $context,
            createdAt: $data['createdAt'],
            attempt: $data['attempt'],
            retryAt: $data['retryAt'] ?? null,
        );
    }

    public function serializeToMeta(AsyncJob $job): string
    {
        /** @var TgAsyncJob $job */
        return json_encode([
            'jobId' => $job->jobId,
            'partitionKey' => $job->partitionKey,
            'context' => [
                'dto' => serialize($job->context->dto),
                'processor' => $job->context->processor,
                'botConfig' => serialize($job->context->botConfig),
                'executionKey' => $job->context->executionKey,
                'jobId' => $job->context->jobId,
                'attempt' => $job->context->attempt,
                'source' => $job->context->source,
                'receivedAt' => $job->context->receivedAt,
            ],
            'attempt' => $job->attempt,
            'createdAt' => $job->createdAt,
            'retryAt' => $job->retryAt,
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }

    public function deserializeFromMeta(string $jobId, array $meta): ?AsyncJob
    {
        $payload = $meta['payload'] ?? null;

        if ($payload === null || $payload === '') {
            return null;
        }

        try {
            $data = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);

            $contextData = $data['context'];

            $context = new UpdateContext(
                dto: unserialize($contextData['dto'], ['allowed_classes' => true]),
                processor: $contextData['processor'],
                botConfig: unserialize($contextData['botConfig'], ['allowed_classes' => true]),
                executionKey: $contextData['executionKey'],
                jobId: $contextData['jobId'],
                attempt: $contextData['attempt'],
                source: $contextData['source'],
                receivedAt: $contextData['receivedAt'],
            );

            return new TgAsyncJob(
                jobId: $jobId,
                partitionKey: $data['partitionKey'],
                context: $context,
                createdAt: $data['createdAt'],
                attempt: (int)$meta['attempt'],
            );
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Full job snapshot for zombie recovery rehydration.
     * Includes: full job state, attempt, and fencing metadata.
     */
    public function serializeToRecoveryPayload(AsyncJob $job, string $fencingToken = ''): string
    {
        $data = json_decode($this->serialize($job), true);

        if ($fencingToken !== '') {
            $data['fencingToken'] = $fencingToken;
        }

        $data['recoveredAt'] = time();

        return json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }
}
