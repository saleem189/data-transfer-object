<?php

namespace Saleem\DataTransferObject\Interfaces;

interface KeyFinderInterface
{
    public static function recursiveFindKey(array $haystack, $needle): bool;
}