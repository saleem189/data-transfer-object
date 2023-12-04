<?php
namespace Saleem\DataTransferObject\Enums;


enum KeyTransformStrategiesEnum: int
{
    case KEYS_TO_CC = 1;    // camel case key transform
    case KEYS_TO_SC = 2;    // snake case key transform
}
