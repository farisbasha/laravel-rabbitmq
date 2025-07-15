<?php

declare(strict_types=1);

namespace HowinCodes\RabbitMQ\Handlers;

use HowinCodes\RabbitMQ\Contracts\HandlerInterface;
use Illuminate\Support\Facades\Log;

final class GenericHandler implements HandlerInterface
{
    public static function handle(string $routingKey, array $payload): void
    {
        Log::info("[GenericHandler] Received {$routingKey}", $payload);
    }
}
