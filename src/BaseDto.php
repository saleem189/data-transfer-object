<?php

namespace Saleem\DataTransferObject;

use Illuminate\Support\Str;
use ReflectionProperty;
use Saleem\DataTransferObject\Enums\KeyTransformStrategiesEnum;
use Saleem\DataTransferObject\Exceptions\ClassNotFoundException;
use Saleem\DataTransferObject\Exceptions\IntersectionTypesNotSupportedException;
use Saleem\DataTransferObject\Exceptions\PropertyMissMatch;
use Saleem\DataTransferObject\KeyTransformStrategies\CamelCaseStrategy;
use Saleem\DataTransferObject\KeyTransformStrategies\SnakeCaseStrategy;


abstract class BaseDto
{
    protected static array $arrayCasts = [];

    public static function build(array|string|null $data): ?static
    {
        if (null === $data) {
            return null;
        }

        if (is_string($data)) {
            $jsonData = json_decode($data, true);
        } else {
            $jsonData = $data;
        }

        $class = static::class;
        $classReflection = new \ReflectionClass($class);
        $properties = self::mapParameters($classReflection->getProperties(), $jsonData, static::$arrayCasts);

        $dto = new static;
        $dto->setData($properties);

        return $dto;
    }

    private static function mapParameters(array $parameters, array $data, array $arrayCasts): array
    {
        $map = [];
        $currentClassParameters = static::getClassProperties($parameters);
        foreach ($parameters as $parameter) {
            $name = $parameter->getName();
            $types = $parameter->getType();

            
            
            if (!static::recursiveFindKey($data, $name) && 'arrayCasts' !== $name) {
                $missingParameter = array_diff(array_keys($data),$currentClassParameters);
                // Handle mismatch or throw an exception
                throw new PropertyMissMatch(reset($missingParameter), class_basename($parameter->class), $name);
            }

            if ($types instanceof \ReflectionUnionType) {
                $typeNames = array_map(fn (\ReflectionNamedType $type) => $type->getName(), $types->getTypes());
            } elseif ($types instanceof \ReflectionNamedType) {
                $typeNames = [$types->getName()];
            } else {
                throw new IntersectionTypesNotSupportedException(gettype($types));
            }

            $nil = new \stdClass();
            $value = $nil;

            if (isset($data[$name])) {
                $value = $data[$name];
            }

            if ($value !== $nil) {
                // Check if the property has an associated DTO class in arrayCasts
                $arrayCastsAttribute = $arrayCasts[$name] ?? null;

                if (is_array($value) && array_is_list($value) && $arrayCastsAttribute) {
                    $arrayItemType = $arrayCastsAttribute;

                    if (class_exists($arrayItemType)) {
                        $value = self::convertArrayToDtoInstances($value, $arrayItemType);
                    }
                } else {
                    $instantiable = null;

                    foreach ($typeNames as $typeName) {
                        if (class_exists($typeName)) {
                            $instantiable = $typeName;
                            break;
                        }
                    }

                    if ($instantiable) {
                        $value = $instantiable::build($value);
                    }
                }

                $map[$name] = $value;
            }
        }

        return $map;
    }

    private static function convertArrayToDtoInstances(array $array, string $class): array
    {
         // Check if the specified class exists
        if (!class_exists($class)) {
            // Handle the scenario where the class does not exist
            // You may log a warning, throw an exception, or take other appropriate actions
            // For now, we'll return an empty array to indicate the failure
            throw new ClassNotFoundException($class);
        }
        // If the class exists, build instances for each item in the array

        return array_map(fn($item) => $class::build($item), $array);
    }

    protected function setData(array $data): void
    {
        foreach ($data as $k => $v) {
            $this->{$k} = $v;
        }
    }

    public function json(): string
    {
        return json_encode($this->getData());
    }

    protected function getData(int $flags = 0): array
    {
        return $this->getDataRecursive((array) $this, $flags);
    }

    protected function getDataRecursive(array $dataBuffer, int $flags = 0): array
    {
        $finalData = [];

        foreach ($dataBuffer as $keyBuffer => $data) {
            $key = self::transformKey($keyBuffer, $flags);

            if (is_object($data) || is_array($data)) {
                $finalData[$key] = $this->getDataRecursive((array) $data, $flags);
            } else {
                $finalData[$key] = $data;
            }
        }

        return $finalData;
    }

    public static function transformKey(string $key, int $flags): string
    {
        if ($flags === KeyTransformStrategiesEnum::KEYS_TO_CC->value ) {
            return CamelCaseStrategy::transform($key);
        } else if ($flags === KeyTransformStrategiesEnum::KEYS_TO_SC->value) {
            return SnakeCaseStrategy::transform($key);
        }

        return $key;
    }

    public function without(string|array $excludeBuffer, bool $asDTO = false, int $flags = 0): array|BaseDto
    {
        $exclude = is_array($excludeBuffer) ? $excludeBuffer : [$excludeBuffer];
        $keys = array_keys(get_object_vars($this));
        if ($asDTO) {
            $finalData = new class extends BaseDto {
            };

            $data = $this;

            foreach ($keys as $keyBuffer) {
                if (in_array($keyBuffer, $exclude)) {
                    continue;
                }
                
                $key = self::transformKey($keyBuffer, $flags);
                
                $finalData->{$key} = $data->{$keyBuffer};
            }
        } else {
            $finalData = [];
            $data = $this->data();
            
            foreach ($keys as $keyBuffer) {
                // dd($data, $keyBuffer);
                if (in_array($keyBuffer, $exclude)) {
                    continue;
                }

                $key = self::transformKey($keyBuffer, $flags);
                $finalData[$key] = $data[$keyBuffer];
            }
        }

        return $finalData;
    }

    public function data(int $flags = 0): array
    {
        return $this->getData($flags);
    }

    public function only(array $keysBuffer, bool $asDTO = false, int $flags = 0): array|BaseDto
    {
        $keys = is_array($keysBuffer) ? $keysBuffer : [$keysBuffer];

        if ($asDTO) {
            $finalData = new class extends BaseDto {
            };

            $data = $this;

            foreach ($keys as $keyBuffer) {
                $key = self::transformKey($keyBuffer, $flags);

                $finalData->{$key} = $data->{$keyBuffer};
            }
        } else {
            $finalData = [];
            $data = $this->data();

            foreach ($keys as $keyBuffer) {
                $key = self::transformKey($keyBuffer, $flags);

                $finalData[$key] = $data[$keyBuffer];
            }
        }

        return $finalData;
    }

    public function __toString(): string
    {
        return json_encode($this->data());
    }

     /**
     * Recursively finds a key in a multidimensional array.
     *
     * @param array $haystack the array to search in
     * @param mixed $needle   the key to find
     *
     * @return bool true if the key is found, false otherwise
     */
    private static function recursiveFindKey(array $haystack, $needle): bool
    {
        // Create an iterator for the array
        $iterator = new \RecursiveArrayIterator($haystack);
        
        // Create a recursive iterator that traverses the array in a depth-first manner
        $recursive = new \RecursiveIteratorIterator(
            $iterator,
            \RecursiveIteratorIterator::SELF_FIRST
        );

        // Loop through each element in the recursive iterator
        foreach ($recursive as $key => $value) {
            // Check if the current key matches the desired key
            if ($key === $needle) {
                return true;
            }
        }

        // If the key is not found, return false
        return false;
    }

    private static function getClassProperties(array $parameters): array {
        $currentParameters = [];
        foreach ($parameters as $parameter) {
            $currentParameters[]=$parameter->getName();
        }
        return $currentParameters;
    }

    public function changeKeys($oldKey, $newKey=null): self
    {
        if (is_array($oldKey)) {
            return $this->changeKeysFromArray($oldKey);
        }

        $data = $this->getData(); // Retrieve the stored data
        $newData = $this->arrayKeyReplace($data, $oldKey, $newKey); // replacing array keys

        // Create a new instance of the DTO and set the modified data
        $newDto = new static();
        $newDto->setData($newData);

        return $newDto;
    }

    private function changeKeysFromArray(array $keyMappings): self
    {
        $data = $this->getData(); // Retrieve the stored data

        foreach ($keyMappings as $oldKey => $newKey) {
            $data = $this->arrayKeyReplace($data, $oldKey, $newKey); // replacing keys
        }

        // Create a new instance of the DTO and set the modified data
        $newDto = new static();
        $newDto->setData($data);

        return $newDto;
    }

    private function arrayKeyReplace($array, $oldKey, $newKey): array
    {
        $newArray = [];

        foreach ($array as $key => $value) {
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
