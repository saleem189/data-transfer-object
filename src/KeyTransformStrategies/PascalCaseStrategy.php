<?php

namespace Saleem\DataTransferObject\KeyTransformStrategies;

use Illuminate\Support\Str;
use Saleem\DataTransferObject\Interfaces\KeyTransformStrategy;

class PascalCaseStrategy implements KeyTransformStrategy
{
    public static function transform(string $key): string
    {
        return Str::ucfirst(Str::camel($key));
    }
}

