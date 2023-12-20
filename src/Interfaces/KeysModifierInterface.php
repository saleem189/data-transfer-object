<?php 

namespace Saleem\DataTransferObject\Interfaces;

use Saleem\DataTransferObject\BaseDto;

interface KeysModifierInterface
{
    public static function includeProperties(string|array $includedKeysBuffer, array $data, bool $asDTO = false, int $flags = 0): array|BaseDto;
    public static function excludeProperties(string|array $excludeKeysBuffer, array $data, bool $asDTO = false, int $flags = 0): array|BaseDto;
}