<?php

namespace Saleem\DataTransferObject;

use Saleem\DataTransferObject\DataHandling\DataMapping;
use Saleem\DataTransferObject\DataHandling\GetData;
use Saleem\DataTransferObject\DataHandling\KeyModifier;
use Saleem\DataTransferObject\KeyTransformStrategies\KeyTransformFactory;

abstract class BaseDto
{
    protected static array $arrayCasts = [];
    public static function build(array|string|null $data): ?static
    {
        $dataMapping = new DataMapping();
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
        $properties = $dataMapping->mapParameters($classReflection->getProperties(), $jsonData, static::$arrayCasts);

        $dto = new static;
        $dto->setData($properties);

        return $dto;
    }


    protected function setData(array $data): void
    {
        foreach ($data as $k => $v) {
            $this->{$k} = $v;
        }
    }

    public function data(int $flags = 0): array
    {
        return GetData::getDataInArray((array) $this, $flags);
    }
    public function json(int $flags = 0): string
    {
        return GetData::getDataInJson((array) $this, $flags);
    }

    public static function transformKey(string $key, int $flags): string
    {
        return KeyTransformFactory::transformKey($key, $flags);
    }

    public function without(string|array $excludeBuffer, bool $asDTO = false, int $flags = 0): array|BaseDto
    {
        return KeyModifier::excludeProperties($excludeBuffer, $this->data(), $asDTO, $flags);
    }

    public function only(string|array $keysBuffer, bool $asDTO = false, int $flags = 0): array|BaseDto
    {
        return KeyModifier::includeProperties($keysBuffer, $this->data(), $asDTO, $flags);
    }

    public function __toString(): string
    {
        return json_encode($this->data());
    }
}
