<?php

namespace Saleem\DataTransferObject\DataHandling;

use Saleem\DataTransferObject\BaseDto;
use Saleem\DataTransferObject\Interfaces\KeysModifierInterface;
use Saleem\DataTransferObject\KeyTransformStrategies\KeyTransformFactory;

class KeyModifier implements KeysModifierInterface
{
    public static function includeProperties(string|array $keysBuffer, array $data, bool $asDTO = false, int $flags = 0): array|BaseDto
    {
        $keys = is_array($keysBuffer) ? $keysBuffer : [$keysBuffer];

        if ($asDTO) {
            $finalData = new class extends BaseDto {
            };

            $currentData = $data;

            foreach ($keys as $keyBuffer) {
                $key = KeyTransformFactory::transformKey($keyBuffer, $flags);

                $finalData->{$key} = $currentData[$keyBuffer] ?? null;
            }
        } else {
            $finalData = [];
            $currentData = $data;

            foreach ($keys as $keyBuffer) {
                $key = KeyTransformFactory::transformKey($keyBuffer, $flags);

                $finalData[$key] = $currentData[$keyBuffer];
            }
        }

        return $finalData;

    }
    public static function excludeProperties(string|array $excludeBuffer, array $data, bool $asDTO = false, int $flags = 0): array|BaseDto
    {
        $exclude = is_array($excludeBuffer) ? $excludeBuffer : [$excludeBuffer];
    
        if ($asDTO) {
            $finalData = new class extends BaseDto {
            };
    
            foreach ($data as $keyBuffer => $value) {
                if (in_array($keyBuffer, $exclude)) {
                    continue;
                }
    
                $key = KeyTransformFactory::transformKey($keyBuffer, $flags);
    
                $finalData->{$key} = $value;
            }
        } else {
            $finalData = [];
    
            foreach ($data as $keyBuffer => $value) {
                if (in_array($keyBuffer, $exclude)) {
                    continue;
                }
    
                $key = KeyTransformFactory::transformKey($keyBuffer, $flags);
    
                $finalData[$key] = $value;
            }
        }
    
        return $finalData;
    }
    
}