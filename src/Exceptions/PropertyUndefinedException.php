<?php

namespace Saleem\DataTransferObject\Exceptions;

class PropertyUndefinedException extends \Exception
{
    public function __construct(string $property, int $code = 0, \Throwable $previous = null)
    {
        parent::__construct("Property undefined: $property", $code, $previous);
    }
}
