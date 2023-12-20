<?php 
namespace Saleem\DataTransferObject\Interfaces;

interface GetDataInterface 
{
    public static function getDataInArray(array $data, int $flags = 0): array;
    public static function getDataInJson(array $data, int $flags = 0): string;
}