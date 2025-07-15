<?php

declare(strict_types=1);

namespace HowinCodes\RabbitMQ\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use HowinCodes\RabbitMQ\Connection\ConnectionManager;
use PhpAmqplib\Wire\AMQPTable;

final class ConsumeQueues extends Command
{
    protected $signature   = 'rabbitmq:consume-queues';
    protected $description = 'Declare, bind & consume all queues with retry';

    public function handle(ConnectionManager $conn): int
    {
        $exchanges = config('rabbitmq.exchanges', []);
        $queues    = config('rabbitmq.queues', []);
        $handlers  = config('rabbitmq.queue_handlers', []);
        $ch        = $conn->channel();

        // 1) Declare exchanges
        foreach ($exchanges as $name => $opts) {
            $ch->exchange_declare(
                $name,
                $opts['type']       ?? 'direct',
                false,
                $opts['durable']    ?? true,
                $opts['auto_delete'] ?? false
            );
        }

        // 2) Declare & bind queues
        foreach ($queues as $queue => $opts) {
            $table = new AMQPTable(
                collect($opts['arguments'] ?? [])
                    ->mapWithKeys(fn($v, $k) => [$k => is_int($v) ? ['I', $v] : ['S', (string)$v]])
                    ->all()
            );

            $ch->queue_declare(
                $queue,
                false,
                $opts['durable'] ?? true,
                false,
                false,
                false,
                $table
            );

            foreach ((array)($opts['routing_keys'] ?? []) as $rk) {
                $ch->queue_bind($queue, $opts['exchange'], $rk);
            }
        }

        // 3) Start consuming
        $ch->basic_qos(null, 1, null);

        foreach (array_keys($queues) as $queue) {
            $handler = $handlers[$queue] ?? null;
            $ch->basic_consume(
                $queue,
                '',
                false,
                false,
                false,
                false,
                $this->makeHandler($ch, $queue, $handler)
            );
            $this->info("Listening on [{$queue}]");
        }

        while ($ch->is_consuming()) {
            $ch->wait();
        }

        return self::SUCCESS;
    }

    private function makeHandler($ch, string $queue, ?string $handlerClass): callable
    {
        return function ($msg) use ($ch, $queue, $handlerClass): void {
            $payload = json_decode($msg->body, true) ?? [];
            $rk      = $msg->delivery_info['routing_key'];

            try {
                if (! $handlerClass || ! method_exists($handlerClass, 'handle')) {
                    throw new \RuntimeException("Handler missing for [{$queue}]");
                }

                $handlerClass::handle($rk, $payload);
                $ch->basic_ack($msg->delivery_info['delivery_tag']);
            } catch (\Throwable $e) {
                Log::error("[{$queue}] {$e->getMessage()}", ['payload' => $msg->body]);
                $ch->basic_nack($msg->delivery_info['delivery_tag'], false, false);
            }
        };
    }
}
