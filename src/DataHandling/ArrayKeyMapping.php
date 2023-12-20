<?php
namespace Saleem\DataTransferObject\DataHandling;

use Saleem\DataTransferObject\Interfaces\ArrayKeyChangeMapping;

class ArrayKeyMapping implements ArrayKeyChangeMapping
{
    public static  function changeKeys(string|array $oldKey, string $newKey=null, array $data,int $flags = 0): array
    {
        $mapKeys = new self();
        if (is_array($oldKey)) {
            return $mapKeys->changeKeysFromArray($oldKey,$data, $flags);
        }

        $newData = $mapKeys->arrayKeyReplace($data, $oldKey, $newKey);
        return $newData;
    }

    private function changeKeysFromArray(array $keyMappings, array $data, int $flags = 0): array
    {

        foreach ($keyMappings as $oldKey => $newKey) {
            $data = $this->arrayKeyReplace($data, $oldKey, $newKey);
        }

        return $data;
    }

    private function arrayKeyReplace( array $data, string $oldKey, string $newKey): array
    {
        $newArray = [];

        foreach ($data as $key => $value) {
            // Replace the key if it matches the old key
            $replacedKey = ($key === $oldKey) ? $newKey : $key;

            // Recursively replace keys in nested arrays
            if (is_array($value)) {
                $value = $this->arrayKeyReplace($value, $oldKey, $newKey);
            }

            $newArray[$replacedKey] = $value;
        }

        return $newArray;
    }
}