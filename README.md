
## Introduction

This package provides a `BaseDTO` class that facilitates handling Data Transfer Objects. It allows easy access to individual properties and conversion to JSON or arrays.

## Installation

To use the this package, follow these steps:

1. **Installation via Composer:**

   ```bash
   composer require saleem/data-transfer-object


```php
class PersonalInfoDto extends BaseDto
{
public ?string $firstName = null;
public ?string $lastName = null;
public ?string $email = null;
}

// Creating an instance of PersonalInfoDto
$personalInfo = PersonalInfoDto::build([
'firstName' => 'John',
'lastName' => 'Doe',
'email' => 'john@example.com'
]);

// Accessing properties
echo $personalInfo->firstName; // Output: John
echo $personalInfo->lastName; // Output: Doe
echo $personalInfo->email; // Output: john@example.com

// Converting to JSON
$jsonData = $personalInfo->json();
echo $jsonData; // Output: {"firstName":"John","lastName":"Doe","email":"john@example.com"}

// Getting data as an array
$dataArray = $personalInfo->data();
print_r($dataArray);
// Output:
// Array (
// [firstName] => John
// [lastName] => Doe
// [email] => john@example.com
// )

// Excluding specific keys
$filteredData = $personalInfo->without('email');
print_r($filteredData);
// Output:
// Array (
// [firstName] => John
// [lastName] => Doe
// )

// Including specific keys
$includedData = $personalInfo->only(['firstName', 'email']);
print_r($includedData);
// Output:
// Array (
// [firstName] => John
// [email] => john@example.com
// )
```
