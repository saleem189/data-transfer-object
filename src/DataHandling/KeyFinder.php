<?php 
namespace Saleem\DataTransferObject\DataHandling;

use Saleem\DataTransferObject\Interfaces\KeyFinderInterface;

class KeyFinder implements KeyFinderInterface
{
    public static function recursiveFindKey(array $haystack, $needle): bool
    {
        $iterator = new \RecursiveArrayIterator($haystack);
        
        // Create a recursive iterator that traverses the array in a depth-first manner
        $recursive = new \RecursiveIteratorIterator(
            $iterator,
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($recursive as $key => $value) {
            // Check if the current key matches the desired key
            if ($key === $needle) {
                return true;
            }
        }

        return false;
    }
}