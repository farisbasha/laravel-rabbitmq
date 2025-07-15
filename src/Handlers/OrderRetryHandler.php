<?php

declare(strict_types=1);

namespace HowinCodes\RabbitMQ\Handlers;

use HowinCodes\RabbitMQ\Contracts\HandlerInterface;
use Illuminate\Support\Facades\Log;

final class OrderRetryHandler implements HandlerInterface
{
    public static function handle(string $routingKey, array $payload): void
    {
        Log::warning("⚠️ OrderRetryHandler final retry:", $payload);
        // e.g. record to dead_letters table or alert team
    }
}
