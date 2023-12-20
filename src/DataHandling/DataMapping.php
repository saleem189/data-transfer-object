<?php

namespace Saleem\DataTransferObject\DataHandling;

use Saleem\DataTransferObject\Exceptions\ClassNotFoundException;
use Saleem\DataTransferObject\Exceptions\IntersectionTypesNotSupportedException as ExceptionsIntersectionTypesNotSupportedException;
use Saleem\DataTransferObject\Exceptions\PropertyMissMatch;
use Saleem\DataTransferObject\Interfaces\DataMapperToObject;

class DataMapping implements DataMapperToObject
{
    public function mapParameters(array $parameters, array $data, array $arrayCasts): array
    {
        $map = [];
        $currentClassParameters = $this->getClassProperties($parameters);
        foreach ($parameters as $parameter) {
            $name = $parameter->getName();
            $types = $parameter->getType();

            if (!KeyFinder::recursiveFindKey($data, $name) && 'arrayCasts' !== $name) {
                $missingParameter = array_diff(array_keys($data),$currentClassParameters);
                // Handle mismatch or throw an exception
                throw new PropertyMissMatch(reset($missingParameter), class_basename($parameter->class), $name);
            }

            if ($types instanceof \ReflectionUnionType) {
                $typeNames = array_map(fn (\ReflectionNamedType $type) => $type->getName(), $types->getTypes());
            } elseif ($types instanceof \ReflectionNamedType) {
                $typeNames = [$types->getName()];
            } else {
                throw new ExceptionsIntersectionTypesNotSupportedException(gettype($types));
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

    public function getClassProperties(array $parameters): array {
        $currentParameters = [];
        foreach ($parameters as $parameter) {
            $currentParameters[]=$parameter->getName();
        }
        return $currentParameters;
    }


    private static function convertArrayToDtoInstances(array $array, string $class): array
    {
        if (!class_exists($class)) {
            throw new ClassNotFoundException($class);
        }

        return array_map(fn($item) => $class::build($item), $array);
    }
}