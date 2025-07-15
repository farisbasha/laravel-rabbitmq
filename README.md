# 📦 howincodes/laravel-rabbitmq

A Laravel-first RabbitMQ wrapper that **auto-configures** dead-letter retry queues so you can focus on writing your handlers—no boilerplate required! 🎉

---

## 🚀 Installation

```bash
composer require howincodes/laravel-rabbitmq
php artisan vendor:publish \
  --provider="HowinCodes\\RabbitMQ\\RabbitMQServiceProvider" \
  --tag="config"
```

This will drop a `config/rabbitmq.php` file into your project.

---

## ⚙️ Configuration

Open `config/rabbitmq.php` and you’ll find:

```php
return [

    // 🖥️ Connection settings
    'host'     => env('RABBITMQ_HOST', '127.0.0.1'),
    'port'     => env('RABBITMQ_PORT', 5672),
    'user'     => env('RABBITMQ_USER', 'guest'),
    'password' => env('RABBITMQ_PASSWORD', 'guest'),
    'vhost'    => env('RABBITMQ_VHOST', '/'),

    // 🔄 Base exchanges
    'exchanges' => [
        'tasks' => ['type'=>'topic', 'durable'=>true],
    ],

    // 📬 Base queues + retry settings
    'queues' => [
        'tasks' => [
            'exchange'     => 'tasks',
            'routing_keys' => ['task.created'],
            'durable'      => true,
            'consume'      => true,

            // ⚙️ Retry configuration
            'retry'        => [
                'enabled'      => true,
                'max_attempts' => 5,
                'interval'     => 15000,
            ],
        ],
    ],

    // 🛠️ Map queue → handler classes
    'queue_handlers' => [
        'tasks'       => HowinCodes\\RabbitMQ\\Handlers\\GenericHandler::class,
        'tasks.retry' => HowinCodes\\RabbitMQ\\Handlers\\GenericRetryHandler::class,
    ],
];
```

> **Note**  
> The `tasks` queue/exchange is just an example. Add as many base queues with retry blocks as you need!

---

## 🔧 Usage

### Publishing a Message

```php
use HowinCodes\\RabbitMQ\\Publisher;

app(Publisher::class)
    ->publish('tasks', 'task.created', [
        'id'      => 123,
        'payload' => ['foo' => 'bar'],
    ]);
```

### Consuming Queues

```bash
php artisan rabbitmq:consume-queues
```

Listens on both `tasks` and `tasks.retry`, dispatching to your handlers.

---

## 🧑‍🤝‍🧑 Another Example: User Queue

```php
// config/rabbitmq.php

'exchanges' => [
    'users' => ['type'=>'topic', 'durable'=>true],
],

'queues' => [
    'users' => [
        'exchange'     => 'users',
        'routing_keys' => ['user.created','user.updated'],
        'durable'      => true,
        'consume'      => true,
        'retry'        => [
            'enabled'      => true,
            'max_attempts' => 3,
            'interval'     => 10000,
        ],
    ],
],

'queue_handlers' => [
    'users'       => HowinCodes\\RabbitMQ\\Handlers\\GenericHandler::class,
    'users.retry' => HowinCodes\\RabbitMQ\\Handlers\\GenericRetryHandler::class,
],
```

---

## ❤️ Thank You
