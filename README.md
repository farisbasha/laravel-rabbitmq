# 📦 howincodes/laravel-rabbitmq

A Laravel-first RabbitMQ wrapper that **auto-configures** dead-letter retry queues so you can focus on writing your handlers—no boilerplate required! 🎉

---

## 🚀 Installation

```bash
# 1. Install via Composer
composer require howincodes/laravel-rabbitmq

# 2. Publish the default config file
php artisan vendor:publish \
  --provider="HowinCodes\RabbitMQ\RabbitMQServiceProvider" \
  --tag="config"
```

````

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
        'orders' => ['type'=>'topic', 'durable'=>true],
        // add as many as you like...
    ],

    // 📬 Base queues + retry settings
    'queues' => [
        'orders' => [
            'exchange'     => 'orders',
            'routing_keys' => ['order.created'],
            'durable'      => true,
            'consume'      => true,

            // ⚙️ Retry configuration
            'retry'        => [
                'enabled'      => true,
                'max_attempts' => 5,       # total tries before giving up
                'interval'     => 15000,   # wait 15s before retry (ms)
            ],
        ],
        // `orders.retry` is auto-generated
    ],

    // 🛠️ Map queue → handler classes
    'queue_handlers' => [
        'orders'       => HowinCodes\RabbitMQ\Handlers\OrderCreatedHandler::class,
        'orders.retry' => HowinCodes\RabbitMQ\Handlers\OrderRetryHandler::class,
    ],
];
```

> **Note**
> The `orders` queue/exchange is just an example to get you started. You can declare any number of base queues, each with its own retry block.

---

## 📚 Detailed Breakdown

1. **Exchanges**

   - Under `exchanges`, list your base exchanges (e.g. `orders`).
   - `<exchange>.retry` is auto-created when retry is enabled.

2. **Queues**

   - Under `queues`, declare each base queue.
   - Add a `retry` section to enable DLX-based retry logic.
   - The package will:

     1. Inject DLX args into your base queue.
     2. Auto-create the retry queue (`<queue>.retry`) with TTL and DLX back to your base exchange.

3. **Handlers**

   - Implement `HowinCodes\RabbitMQ\Contracts\HandlerInterface` in your classes.
   - Map them under `queue_handlers` for both base and retry queues.

---

## 🔧 Usage

### Publishing a Message

```php
use HowinCodes\RabbitMQ\Publisher;

// anywhere in your app
app(Publisher::class)
    ->publish('orders', 'order.created', [
        'id'    => 42,
        'total' => 99.95,
    ]);
```

This will declare the `orders` exchange (if needed) and send a persistent message.

### Consuming Queues

```bash
php artisan rabbitmq:consume-queues
```

- Listens on **both** `orders` and `orders.retry` queues.
- Routes messages to your configured handlers.
- On exception in `OrderCreatedHandler`, the message moves to `orders.retry` automatically.
- `orders.retry` waits for your `interval`, then re-publishes back to `orders`—up to `max_attempts`.

---

## 🧑‍🤝‍🧑 User Queue Example

Below is an alternate example showing a **user** queue setup:

```php
// config/rabbitmq.php

return [

    'exchanges' => [
        'users' => ['type'=>'topic', 'durable'=>true],
    ],

    'queues' => [
        'users' => [
            'exchange'     => 'users',
            'routing_keys' => ['user.created', 'user.updated', 'user.deleted'],
            'durable'      => true,
            'consume'      => true,
            'retry'        => [
                'enabled'      => true,
                'max_attempts' => 3,
                'interval'     => 10000,   # 10s before retry
            ],
        ],
    ],

    'queue_handlers' => [
        'users'       => HowinCodes\RabbitMQ\Handlers\UserEventHandler::class,
        'users.retry' => HowinCodes\RabbitMQ\Handlers\UserRetryHandler::class,
    ],
];
```

### Publishing a User Event

```php
use HowinCodes\RabbitMQ\Publisher;

// create or update user...
app(Publisher::class)
    ->publish('users', 'user.created', [
        'id'    => 123,
        'name'  => 'Alice',
        'email' => 'alice@example.com',
    ]);
```

### Consuming User Queues

```bash
php artisan rabbitmq:consume-queues
```

- **UserEventHandler** handles normal user events.
- On failure, messages move to `users.retry`.
- **UserRetryHandler** handles final-failure user messages.

---

## 🎨 Customization & Advanced

- **Multiple Queues**: declare as many base queues as needed.
- **Custom DLX Names**: override `retry.exchange` or `retry.routing_key`.
- **Disable Retry**: set `'retry' => ['enabled' => false]`.
- **Monitoring**: integrate with Laravel logging or APM to track dead-letter flows.

---

## ❤️ Thank You

Thanks for using **howincodes/laravel-rabbitmq**!
If you find this helpful, ⭐ star the repo on GitHub and share your feedback.
Happy messaging! 🚀
````
