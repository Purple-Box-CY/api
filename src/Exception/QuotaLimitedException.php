<?php

namespace App\Exception;

class QuotaLimitedException extends \Exception
{
    public function __construct(string $message = 'Limit is exceeded', int $code = 402, \Throwable $previous = null,)
    {
        parent::__construct($message, $code, $previous);
    }
}
