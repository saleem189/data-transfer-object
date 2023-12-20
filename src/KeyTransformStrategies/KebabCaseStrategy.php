<?php

namespace Saleem\DataTransferObject\KeyTransformStrategies;

use Illuminate\Support\Str;
use Saleem\DataTransferObject\Interfaces\KeyTransformStrategy;

class KebabCaseStrategy implements KeyTransformStrategy
{
    public static function transform(string $key): string
    {
        return Str::kebab(Str::camel($key));
    }
}

