<?php

declare(strict_types=1);

return [

    'host'     => env('RABBITMQ_HOST', '127.0.0.1'),
    'port'     => env('RABBITMQ_PORT', 5672),
    'user'     => env('RABBITMQ_USER', 'guest'),
    'password' => env('RABBITMQ_PASSWORD', 'guest'),
    'vhost'    => env('RABBITMQ_VHOST', '/'),

    'exchanges' => [
        'tasks' => ['type'=>'topic', 'durable'=>true],
    ],

    'queues' => [
        'tasks' => [
            'exchange'     => 'tasks',
            'routing_keys' => ['task.created'],
            'durable'      => true,
            'consume'      => true,
            'retry'        => [
                'enabled'      => true,
                'max_attempts' => 5,
                'interval'     => 15000,
            ],
        ],
    ],

    'queue_handlers' => [
        'tasks'       => HowinCodes\RabbitMQ\Handlers\GenericHandler::class,
        'tasks.retry' => HowinCodes\RabbitMQ\Handlers\GenericRetryHandler::class,
    ],
];
