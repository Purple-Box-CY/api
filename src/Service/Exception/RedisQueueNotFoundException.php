<?php

namespace App\Service\Exception;

use Exception;
use Throwable;

class RedisQueueNotFoundException extends Exception
{
    public function __construct(string $queue, string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        if (!$message) {
            $message = sprintf('Redis queue "%s" not found', $queue);
        }
        parent::__construct($message, $code, $previous);
    }
}