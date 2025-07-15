<?php

declare(strict_types=1);

namespace HowinCodes\RabbitMQ\Contracts;

/**
 * Must be implemented by any queue message handler.
 */
interface HandlerInterface
{
    /**
     * Handle an incoming message.
     *
     * @param  string  $routingKey
     * @param  array   $payload
     */
    public static function handle(string $routingKey, array $payload): void;
}
