<?php

declare(strict_types=1);

return [

    // Connection
    'host'     => env('RABBITMQ_HOST', '127.0.0.1'),
    'port'     => env('RABBITMQ_PORT', 5672),
    'user'     => env('RABBITMQ_USER', 'guest'),
    'password' => env('RABBITMQ_PASSWORD', 'guest'),
    'vhost'    => env('RABBITMQ_VHOST', '/'),

    // Base exchanges
    'exchanges' => [
        'orders' => [
            'type'    => 'topic',
            'durable' => true,
        ],
    ],

    // Declare your base queues + retry settings
    'queues' => [
        'orders' => [
            'exchange'     => 'orders',
            'routing_keys' => ['order.created'],
            'durable'      => true,
            'consume'      => true,
            'retry'        => [
                'enabled'      => true,
                'max_attempts' => 5,
                'interval'     => 15000,   // in ms
            ],
        ],
    ],

    // Map queue → handler
    'queue_handlers' => [
        'orders'       => HowinCodes\RabbitMQ\Handlers\OrderCreatedHandler::class,
        'orders.retry' => HowinCodes\RabbitMQ\Handlers\OrderRetryHandler::class,
    ],
];
