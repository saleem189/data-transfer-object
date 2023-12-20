<?php
namespace Saleem\DataTransferObject\DataHandling;

use Saleem\DataTransferObject\Interfaces\GetDataInterface;
use Saleem\DataTransferObject\KeyTransformStrategies\KeyTransformFactory;

class GetData implements GetDataInterface
{
    public static function getDataInArray(array $data, int $flags = 0): array
    {
        return (new self())->getData($data, $flags);
    }
    public static function getDataInJson(array $data, int $flags = 0): string
    {
        return json_encode((new self())->getData($data, $flags));
    }

    private function getData(array $data, int $flags = 0): array
    {
        return $this->getDataRecursive($data, $flags);
    }

    private function getDataRecursive(array $dataBuffer, int $flags = 0): array
    {
        $finalData = [];

        foreach ($dataBuffer as $keyBuffer => $data) {
            $key = KeyTransformFactory::transformKey($keyBuffer, $flags);

            if (is_object($data) || is_array($data)) {
                $finalData[$key] = $this->getDataRecursive((array) $data, $flags);
            } else {
                $finalData[$key] = $data;
            }
        }

        return $finalData;
    }
}