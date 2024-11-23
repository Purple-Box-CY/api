<?php

namespace App\Exception;

class FormatNotSupportedException extends \Exception
{
    public function __construct(string $message = 'Format not supported', int $code = 406, \Throwable $previous = null,)
    {
        parent::__construct($message, $code, $previous);
    }
}
