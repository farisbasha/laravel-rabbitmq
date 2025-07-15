<?php

declare(strict_types=1);

namespace HowinCodes\RabbitMQ;

use Illuminate\Support\ServiceProvider;
use HowinCodes\RabbitMQ\Connection\ConnectionManager;
use HowinCodes\RabbitMQ\Console\ConsumeQueues;

final class RabbitMQServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/rabbitmq.php',
            'rabbitmq'
        );

        // Expand retry rules
        $cfg = config('rabbitmq');
        ['exchanges' => $ex, 'queues' => $qs] = QueueConfigurator::build($cfg);
        config()->set('rabbitmq.exchanges', $ex);
        config()->set('rabbitmq.queues', $qs);

        $this->app->singleton(
            ConnectionManager::class,
            fn() => new ConnectionManager()
        );

        $this->app->singleton(
            Publisher::class,
            fn($app) => new Publisher($app->make(ConnectionManager::class))
        );

        $this->commands([ConsumeQueues::class]);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/rabbitmq.php' => config_path('rabbitmq.php'),
        ], 'config');
    }
}
