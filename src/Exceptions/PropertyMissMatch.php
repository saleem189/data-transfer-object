<?php 
namespace Saleem\DataTransferObject\Exceptions;

class PropertyMissMatch extends \Exception
{
    protected const PROPERTY_MISS_MATCH = "Error: Property '%s' Miss Match. Plese ensure that the property is properly defined and included in the Class '%s'.";

    public function __construct(string $propertyName = '', string $className = '',string $classProperty = '', int $code = 0, \Throwable $previous = null)
    {
        parent::__construct(sprintf(self::PROPERTY_MISS_MATCH, $propertyName, $className, $classProperty), $code, $previous);
    }
}
