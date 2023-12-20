<?php
namespace Saleem\DataTransferObject\Enums;


enum KeyTransformStrategiesEnum: int
{
    case KEYS_TO_CC = 1;    // camel case key transform
    case KEYS_TO_SC = 2;    // snake case key transform
    case KEYS_TO_KC = 3;    // kebab case key transform
    case KEYS_TO_PC = 4;    // pascal case key transform
}
