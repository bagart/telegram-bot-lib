<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Outbound;

/**
 * Outbound middleware pipeline (PSR-15 style, synchronous, void return).
 *
 * Chain is folded right-to-left into a linked list of named
 * {@see OutboundNextHandler} nodes terminated by {@see OutboundTerminalHandler}.
 * Call order = order in $middlewares array (first = outermost, last = executor).
 *
 * Order:
 *   ExpiryMiddleware → RetryBudgetMiddleware → RateLimitMiddleware
 *   → TelegramOutboundExecutor
 *
 * Ordering is now handled by the queue implementation (OutboundOrderingQueueContract),
 * not by middleware. The pipeline is simpler and faster.
 *
 * Exceptions ({@see OutboundRetryException} / {@see OutboundSkipException} /
 * {@see OutboundBusinessErrorException}) bubble up to Worker without interception —
 * the pipeline does not wrap or swallow control flow.
 */
final class OutboundPipeline
{
    /**
     * @param  OutboundMiddleware[]  $middlewares  Ordered list (first = outermost).
     */
    public function __construct(
        private readonly array $middlewares,
    ) {
    }

    /**
     * Run the envelope through the entire middleware chain.
     *
     * @throws OutboundRetryException Middleware requested a retry.
     * @throws OutboundSkipException Middleware decided to discard the task.
     * @throws OutboundBusinessErrorException Middleware recorded a business error.
     */
    public function execute(OutboundEnvelope $envelope): void
    {
        $handler = new OutboundTerminalHandler();

        foreach (array_reverse($this->middlewares) as $middleware) {
            $handler = new OutboundNextHandler($middleware, $handler);
        }

        $handler->handle($envelope);
    }
}
