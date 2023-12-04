<?php
namespace Saleem\DataTransferObject\Interfaces;

use Saleem\DataTransferObject\KeyTransformStrategies\CamelCaseStrategy;
use Saleem\DataTransferObject\KeyTransformStrategies\SnakeCaseStrategy;

interface KeyTransformStrategy
{
    public static function transform(string $key): string;
}
