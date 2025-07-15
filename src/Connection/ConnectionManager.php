<?php

declare(strict_types=1);

namespace HowinCodes\RabbitMQ\Connection;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Channel\AMQPChannel;

final class ConnectionManager
{
    private AMQPChannel $channel;

    public function __construct()
    {
        $conn = new AMQPStreamConnection(
            config('rabbitmq.host'),
            (int) config('rabbitmq.port'),
            config('rabbitmq.user'),
            config('rabbitmq.password'),
            config('rabbitmq.vhost')
        );

        $this->channel = $conn->channel();
    }

    public function channel(): AMQPChannel
    {
        return $this->channel;
    }

    public function close(): void
    {
        $this->channel->close();
    }
}
