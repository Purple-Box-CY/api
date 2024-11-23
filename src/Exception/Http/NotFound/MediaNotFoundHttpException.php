<?php

namespace App\Exception\Http\NotFound;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MediaNotFoundHttpException extends NotFoundHttpException
{
    public function __construct(string $message = 'Media not found', \Throwable $previous = null, int $code = 0, array $headers = [])
    {
        parent::__construct($message, $previous, $code, $headers);
    }
}
