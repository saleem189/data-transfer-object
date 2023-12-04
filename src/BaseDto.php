<?php

namespace Saleem\DataTransferObject;

use Illuminate\Support\Str;
use ReflectionProperty;
use Saleem\DataTransferObject\Enums\KeyTransformStrategiesEnum;
use Saleem\DataTransferObject\Exceptions\ClassNotFoundException;
use Saleem\DataTransferObject\Exceptions\IntersectionTypesNotSupportedException;
use Saleem\DataTransferObject\KeyTransformStrategies\CamelCaseStrategy;
use Saleem\DataTransferObject\KeyTransformStrategies\SnakeCaseStrategy;

define('KEYS_TO_CC', 1 << 0);
define('KEYS_TO_SC', 1 << 1);

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

        foreach ($parameters as $parameter) {
            $name = $parameter->getName();
            $types = $parameter->getType();

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
                $finalData[$key] = $this->getDataRecursive((array) $data);
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
}
