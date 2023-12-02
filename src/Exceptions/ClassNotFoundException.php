<?php

namespace Saleem\DataTransferObject\Exceptions;

class ClassNotFoundException extends \Exception
{
    public function __construct(string $message = '', int $code = 0, \Throwable $previous = null)
    {
        $message = 'Class not found: ' . $message;
        parent::__construct($message, $code, $previous);
    }
}
