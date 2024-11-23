<?php

namespace App\Exception\Http\RunTimeError;

use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

class RunTimeHttpException extends ServiceUnavailableHttpException
{
    public function __construct(int|string $retryAfter = null, string $message = 'Run time error', \Throwable $previous = null, int $code = 0, array $headers = [])
    {
        parent::__construct($retryAfter, $message, $previous, $code, $headers);
    }
}
