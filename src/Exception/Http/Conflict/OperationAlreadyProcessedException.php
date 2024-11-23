<?php

namespace App\Exception\Http\Conflict;

use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class OperationAlreadyProcessedException extends ConflictHttpException
{
    public function __construct(string $message = 'Operation already processed', \Throwable $previous = null, int $code = 0, array $headers = [])
    {
        parent::__construct($message, $previous, $code, $headers);
    }
}
