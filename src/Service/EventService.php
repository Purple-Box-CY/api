<?php

namespace App\Service;

use App\Service\Infrastructure\LogService;
use App\Service\Infrastructure\RedisService;

class EventService
{
    public function __construct(
        private RedisService $redisService,
        private LogService   $logger,
    ) {
    }

    public function addEventToQueue(
        string $queue,
        array  $data,
    ): void {
        try {
            $this->redisService->pushToQueue($queue, serialize($data));
        } catch (\Exception $e) {
            $this->logger->error('Failed to add event to queue',
                [
                    'queue' => $queue,
                    'data'  => $data,
                    'error' => $e->getMessage(),
                ]);
        }
    }
}