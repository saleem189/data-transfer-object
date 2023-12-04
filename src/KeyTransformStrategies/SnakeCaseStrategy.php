<?php

namespace Saleem\DataTransferObject\KeyTransformStrategies;

use Illuminate\Support\Str;
use Saleem\DataTransferObject\Interfaces\KeyTransformStrategy;

class SnakeCaseStrategy implements KeyTransformStrategy
{
    public static function transform(string $key): string
    {
        return Str::snake($key);
    }
}

