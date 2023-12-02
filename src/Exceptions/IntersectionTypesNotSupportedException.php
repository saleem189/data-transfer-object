<?php

namespace Saleem\DataTransferObject\Exceptions;

class IntersectionTypesNotSupportedException extends \Exception
{
    public function __construct(string $type, int $code = 0, \Throwable $previous = null)
    {
        $message =" Unsupported type encountered: " . gettype($type);
        parent::__construct($message, $code, $previous);
    }
}
