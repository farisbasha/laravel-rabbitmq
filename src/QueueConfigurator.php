<?php

declare(strict_types=1);

namespace HowinCodes\RabbitMQ;

use Illuminate\Support\Arr;

final class QueueConfigurator
{
    /**
     * Expand base queues with retry rules into full
     * exchanges + queues definitions.
     */
    public static function build(array $config): array
    {
        $exchanges = $config['exchanges'] ?? [];
        $queues    = $config['queues'] ?? [];

        foreach ($queues as $name => $opts) {
            $retryOpts = $opts['retry'] ?? [];
            if (! empty($retryOpts['enabled'])) {
                $baseExchange = $opts['exchange'];
                $dlxExchange  = $retryOpts['exchange']    ?? $baseExchange . '.retry';
                $dlxQueue     = $name . '.retry';
                $dlxRouting   = $retryOpts['routing_key'] ?? $dlxQueue;

                // 1) Add DLX exchange
                $exchanges[$dlxExchange] = [
                    'type'    => $retryOpts['type']    ?? 'direct',
                    'durable' => $retryOpts['durable'] ?? true,
                ];

                // 2) Attach DLX args to base queue
                $queues[$name]['arguments'] = [
                    'x-dead-letter-exchange'    => $dlxExchange,
                    'x-dead-letter-routing-key' => $dlxRouting,
                ];

                // 3) Auto-create retry queue
                $queues[$dlxQueue] = [
                    'exchange'     => $dlxExchange,
                    'routing_keys' => [$dlxRouting],
                    'durable'      => $retryOpts['durable'] ?? true,
                    'consume'      => true,
                    'arguments'    => [
                        'x-message-ttl'             => $retryOpts['interval'],
                        'x-dead-letter-exchange'    => $baseExchange,
                        'x-dead-letter-routing-key' => Arr::first($opts['routing_keys']) ?? '',
                    ],
                ];
            }
        }

        return [
            'exchanges' => $exchanges,
            'queues'    => $queues,
        ];
    }
}
