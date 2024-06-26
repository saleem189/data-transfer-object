
## Introduction

This package facilitates handling Data Transfer Objects. It allows easy access to individual properties and conversion to JSON or arrays.

## Installation

To use the this package, follow these steps:

1. **Installation via Composer:**

   ```bash
   composer require saleem/data-transfer-object
2. For config file publishing (this will publish file in config under "data-transfer-object.php") from there you can change NameSpace
```bash
   php artisan vendor:publish --tag=config-file
```
## Creating new DTO class
1. **Using Command**
    ```bash
    php artisan make:dto PersonalInfo
2. **Creating without command and extending it from BaseDto**
    ```bash
    <?php

    use Saleem\DataTransferObject\BaseDto;

    class PersonalInfoDto extends BaseDto
    {
        public ?string $firstName = null;
        public ?string $lastName = null;
        public ?string $email = null;
    }
#### this will create a new DTO class in app/Common/DTO (created using command)
```php
namespace App\Common\DTO;

use Saleem\DataTransferObject\BaseDto;

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

//Storing data in Table
$dataArray = $personalInfo->data();
PersnonalInfo::create($dataArray);

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
#### Changing Keys and assigining new keys

```php
$data = CreateCarrierDTO::build([
    'auth0_user_id' => '21sqwaW',
    'some_id' => 1,
    'email' => 'email@gmail.com',
    'name' => 'name',
    'mc_number' => 'qweqwe',
    'dot_number' => 1,
    'some_status' => 'some_status',
]);

$keysPassedAsArray = [
    'auth0_user_id' => 'NewAuthId',
    'some_id' => 'NewSomeId',
];
$passedAsArray = $data->changeKeys($keysPassedAsArray);
// array(7) {
//     ["NewAuthId"]=>          Changed        
//     string(7) "21sqwaW"
//     ["NewSomeId"]=>         Changed
//     int(1)
//     ["email"]=>
//     string(17) "email@gmail.com"
//     ["name"]=>
//     string(4) "name"
//     ["mc_number"]=>
//     string(6) "qweqwe"
//     ["dot_number"]=>
//     int(1)
//     ["some_status"]=>
//     string(12) "some_status"
//   }
$passingSingleKey = $data->changeKeys('auth0_user_id', 'NewAuthId');
// array(7) {
//     ["NewAuthId"]=>          Changed
//     string(7) "21sqwaW"
//     ["some_id"]=>
//     int(1)
//     ["email"]=>
//     string(17) "email@gmail.com"
//     ["name"]=>
//     string(4) "name"
//     ["mc_number"]=>
//     string(6) "qweqwe"
//     ["dot_number"]=>
//     int(1)
//     ["some_status"]=>
//     string(12) "some_status"
//   }
```

## Using different DTO's in a single container 
### Suppose these classes are created
```php
class AddressDto extends BaseDto
{
    public ?string $street = null;
    public ?string $city = null;
    public ?string $state = null;
    public ?string $zipCode = null;
}

class CreditCardDto extends BaseDto
{
    public ?string $cardNumber = null;
    public ?string $expiryDate = null;
    public ?string $cardHolderName = null;
}

class EducationalInfoDto extends BaseDto
{
    public ?string $schoolName = null;
    public ?string $degree = null;
    public ?int $graduationYear = null;
}

class PersonalInfoDto extends BaseDto
{
    public ?string $firstName = null;
    public ?string $lastName = null;
    public ?string $email = null;
} 

```

#### Using Arrays of DTOs within DTOs:

When utilizing arrays of the same DTO within a larger DTO class, it's beneficial to cast them into relevant DTO classes using array casting ($arrayCasts).

Benefits:

    Structured Data: Maintain a consistent structure by ensuring arrays hold instances of specific DTO classes.

    Encapsulation: Simplify data access and manipulation by grouping related elements together.

    Consistency: Establish a clear and standardized format for improved readability.

    Enhanced Functionality: Leverage specific functionalities defined within DTO classes.

    Data Validation: Enforce type safety and validation rules for better data integrity.

Casting arrays into relevant DTO classes enhances readability, maintains structure, and provides better control over data management within your DTOs.
### Now using different DTO's in a single container

```php
class StudentDto extends BaseDto
{
    public ?PersonalInfoDto $personalInfo = null;
    public array $addresses = [];
    public ?EducationalInfoDto $educationalInfo = null;
    public array $creditCards = [];

protected static array $arrayCasts = [
        'addresses' => AddressDto::class,
        'creditCards' => CreditCardDto::class
    ];
}


$studentData = [
    'personalInfo' => ['firstName' => 'John', 'lastName' => 'Doe', 'email' => 'john@example.com'],
    'addresses' => [
        ['street' => '123 Main St', 'city' => 'City1', 'state' => 'State1', 'zipCode' => '12345'],
        ['street' => '456 Second St', 'city' => 'City2', 'state' => 'State2', 'zipCode' => '67890'],
    ],
    'educationalInfo' => ['schoolName' => 'University of Example', 'degree' => 'Computer Science', 'graduationYear' => 2023],
    'creditCards' => [
        ['cardNumber' => '1234-5678-9012-3456', 'expiryDate' => '12/25', 'cardHolderName' => 'John Doe'],
        ['cardNumber' => '9876-5432-1098-7654', 'expiryDate' => '06/24', 'cardHolderName' => 'Jane Doe'],
    ],
];

$studentDto = StudentDto::build($studentData);
// Converting StudentDto to JSON
$studentJson = $studentDto->json();
echo $studentJson;

// Excluding specific keys from StudentDto
$filteredStudent = $studentDto->without(['addresses', 'creditCards']);
// Check if $filteredStudent is a BaseDto object
if ($filteredStudent instanceof BaseDto) {
    // If it's a BaseDto object, use the json() method
    $filteredStudentJson = $filteredStudent->json();
    echo $filteredStudentJson;
} else {
    // If it's an array, convert it to JSON directly
    $filteredStudentJson = json_encode($filteredStudent);
    echo $filteredStudentJson;
}


// Including specific keys from StudentDto
$includedStudent = $studentDto->only(['personalInfo', 'educationalInfo']);
// Check if $filteredStudent is a BaseDto object
if ($includedStudent instanceof BaseDto) {
    // If it's a BaseDto object, use the json() method
    $includedStudentJson = $includedStudent->json();
    echo $includedStudentJson;
} else {
    // If it's an array, convert it to JSON directly
    $includedStudentJson = json_encode($includedStudent);
    echo $includedStudentJson;
}

foreach ($studentDto->addresses as $address) {
    echo "- {$address->street}, {$address->city}, {$address->state}, {$address->zipCode}\n";
}
```
## Using in Request (Take Previous Example of Single Container for StudentDto)

### Json Payload
```json
{
    "personalInfo": {
        "firstName": "John",
        "lastName": "Doe",
        "email": "john@example.com"
    },
    "addresses": [
        {
            "street": "123 Main St",
            "city": "City1",
            "state": "State1",
            "zipCode": "12345"
        },
        {
            "street": "456 Second St",
            "city": "City2",
            "state": "State2",
            "zipCode": "67890"
        }
    ],
    "educationalInfo": {
        "schoolName": "University of Example",
        "degree": "Computer Science",
        "graduationYear": 2023
    },
    "creditCards": [
        {
            "cardNumber": "1234-5678-9012-3456",
            "expiryDate": "12/25",
            "cardHolderName": "John Doe"
        },
        {
            "cardNumber": "9876-5432-1098-7654",
            "expiryDate": "06/24",
            "cardHolderName": "Jane Doe"
        }
    ]
}

```
### Api.web

```php
Route::post('/test', function(Request $request){

$studentDto = StudentDto::build($request->all());
// Converting StudentDto to JSON
$studentJson = $studentDto->json();
echo $studentJson;

// Excluding specific keys from StudentDto
$filteredStudent = $studentDto->without(['addresses', 'creditCards']);
// Check if $filteredStudent is a BaseDto object
if ($filteredStudent instanceof BaseDto) {
    // If it's a BaseDto object, use the json() method
    $filteredStudentJson = $filteredStudent->json();
    echo $filteredStudentJson;
} else {
    // If it's an array, convert it to JSON directly
    $filteredStudentJson = json_encode($filteredStudent);
    echo $filteredStudentJson;
}


// Including specific keys from StudentDto
$includedStudent = $studentDto->only(['personalInfo', 'educationalInfo']);
// Check if $filteredStudent is a BaseDto object
if ($includedStudent instanceof BaseDto) {
    // If it's a BaseDto object, use the json() method
    $includedStudentJson = $includedStudent->json();
    echo $includedStudentJson;
} else {
    // If it's an array, convert it to JSON directly
    $includedStudentJson = json_encode($includedStudent);
    echo $includedStudentJson;
}

foreach ($studentDto->addresses as $address) {
    echo "- {$address->street}, {$address->city}, {$address->state}, {$address->zipCode}\n";
}
});
```
