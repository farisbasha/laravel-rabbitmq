<?php

declare(strict_types=1);

namespace HowinCodes\RabbitMQ;

use HowinCodes\RabbitMQ\Connection\ConnectionManager;
use PhpAmqpLib\Message\AMQPMessage;
use InvalidArgumentException;

final class Publisher
{
    public function __construct(private ConnectionManager $conn) {}

    public function publish(string $exchange, string $routingKey, array $payload): void
    {
        $exchanges = config('rabbitmq.exchanges', []);
        if (! isset($exchanges[$exchange])) {
            throw new InvalidArgumentException("Exchange [{$exchange}] not found.");
        }

        $opts = $exchanges[$exchange];
        $ch   = $this->conn->channel();

        $ch->exchange_declare(
            $exchange,
            $opts['type']       ?? 'direct',
            false,
            $opts['durable']    ?? true,
            $opts['auto_delete'] ?? false
        );

        $msg = new AMQPMessage(
            json_encode($payload, JSON_THROW_ON_ERROR),
            ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]
        );

        $ch->basic_publish($msg, $exchange, $routingKey);
        $this->conn->close();
    }
}
