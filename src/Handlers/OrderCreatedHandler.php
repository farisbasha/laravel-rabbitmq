<?php

declare(strict_types=1);

namespace HowinCodes\RabbitMQ\Handlers;

use HowinCodes\RabbitMQ\Contracts\HandlerInterface;
use Illuminate\Support\Facades\Log;

final class OrderCreatedHandler implements HandlerInterface
{
    public static function handle(string $routingKey, array $payload): void
    {
        Log::info("✅ OrderCreatedHandler received:", $payload);
        // e.g. Order::create($payload);
        // throw new \Exception("Simulate failure");
    }
}
