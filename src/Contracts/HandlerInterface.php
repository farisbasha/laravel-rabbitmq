<?php

declare(strict_types=1);

namespace HowinCodes\RabbitMQ\Contracts;

/**
 * Implement to handle messages from a queue.
 */
interface HandlerInterface
{
    /**
     * @param string $routingKey
     * @param array  $payload
     */
    public static function handle(string $routingKey, array $payload): void;
}
