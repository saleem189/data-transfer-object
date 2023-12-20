<?php

namespace Saleem\DataTransferObject\KeyTransformStrategies;

use Saleem\DataTransferObject\Enums\KeyTransformStrategiesEnum;

class KeyTransformFactory
{
    public static function transformKey(string $key, int $flags = 0): string
    {
        // dump($key);
        if (empty($key) && !is_numeric($key)) {
            throw new \InvalidArgumentException("Key must be a non-empty string.");
        }

       switch ($flags) {
            case KeyTransformStrategiesEnum::KEYS_TO_CC->value:
                return CamelCaseStrategy::transform($key);
            case KeyTransformStrategiesEnum::KEYS_TO_SC->value:
                return SnakeCaseStrategy::transform($key);
            case KeyTransformStrategiesEnum::KEYS_TO_KC->value:
                return KebabCaseStrategy::transform($key);
            case KeyTransformStrategiesEnum::KEYS_TO_PC->value:
                return PascalCaseStrategy::transform($key);
            default:
                return $key;
        }
        
    }
}

