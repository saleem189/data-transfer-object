<?php
namespace Saleem\DataTransferObject\Interfaces;

interface ArrayKeyChangeMapping
{
    public static  function changeKeys(string|array $oldKey, string $newKey=null,array $data, int $flags = 0): array;
}