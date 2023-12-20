<?php
namespace Saleem\DataTransferObject\Interfaces;

interface DataMapperToObject
{
    public function mapParameters(array $parameters, array $data, array $arrayCasts): array;
    public function getClassProperties(array $parameters): array;
}