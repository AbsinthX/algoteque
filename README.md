# Algoteque - technical task

### Author: Konrad Ptak
### Time spent: 4-5h

## Overview

This repository contains the implementation of a course bundle recommendation system designed as part of a technical recruitment task for Algoteque. The system generates resource bundle quotes based on teacher requests and provider offerings. The application is built using Symfony and adheres to modern software development principles.

## Features

* ### Request Validation:
Validates incoming JSON requests to ensure the data structure is correct and contains valid topics and resource counts.
* ### Provider Matching:
Matches teacher's requested topics with available provider topics using a rule-based approach.
* ### Quote Calculation:
Calculates quotes for providers based on the number of requested resources and pre-defined rules using various strategies.
* ### Error Handling:
Handles invalid JSON, missing fields, and unexpected errors gracefully with proper HTTP responses.
* ### Extensible Design:
Supports adding new strategies for quote calculation without modifying existing code.
* ### Endpoint /quote:
Accepts teacher's request in the following format:

```json
{
    "topics": {
        "reading": 20,
        "math": 50,
        "science": 30,
        "history": 15,
        "art": 10
    }
}
```

Returns the following response:

```json
{
  "toptopics": {
    "math": 50,
    "science": 30,
    "reading": 20
  },
  "matches": {
    "provider_a": [
      "math",
      "science"
    ],
    "provider_b": [
      "science",
      "reading"
    ],
    "provider_c": [
      "math"
    ]
  },
  "quotes": {
    "provider_a": 8,
    "provider_b": 5,
    "provider_c": 10
  }
}
```

## Approach and Design
### Technologies Used
* #### PHP 8.4:
Leveraged modern features such as attributes, enums, and typed properties.
* #### Symfony Framework:
Used for routing, dependency injection, validation, and overall application structure.
* #### PHPUnit:
Comprehensive unit tests were written to ensure code correctness and edge-case coverage.

### Patterns and Practices
* #### Dependency Injection:
All dependencies, such as repositories and services, are injected to promote testability and decoupling.
* #### Strategy Pattern:
Used to implement flexible quote calculation logic. Different strategies handle different rules for calculating quotes.
* #### Repository Pattern:
Encapsulates data access logic (e.g., reading provider data from a JSON file).
* #### Separation of Concerns:
Application logic is split into services (ProviderMatchingService, QuoteCalculationService), keeping controllers thin and focused on HTTP concerns.
* #### Validation:
Symfony Validator was used to ensure the correctness of incoming requests.
* #### Test-Driven Development:
Key functionalities were developed with comprehensive unit and functional tests to ensure reliability and correctness.

## How to Run the Project
### Prerequisites
* PHP 8.4 or higher 
* Composer
* Symfony CLI (optional but recommended)

### Setup Instructions
* Clone the repository:

```bash
git clone https://github.com/your-repo.git
cd your-repo
```
* Install dependencies:

```bash
composer install
```
* Run the Symfony development server:

```bash
symfony server:start
```
* The project is based on a bare Symfony skeleton. To test the application, you should send a POST request to the **/quote** endpoint.
```bash
curl -X POST http://localhost:8000/quote \
-H "Content-Type: application/json" \
-d '{
    "topics": {
        "reading": 20,
        "math": 50,
        "science": 30,
        "history": 15,
        "art": 10
    }
}'
```

You can also use Postman.

## Improvements

Given more time, I would:

* Implement detailed error logging for easier debugging.
* Add an API documentation tool like Swagger to describe available endpoints.
* Expand test coverage to include more edge cases and integration scenarios.


# Improvements after feedback

I've added a comment explaining the use of constructor property promotion in: <br>
[ProviderRepository.php](src/Repository/ProviderRepository.php)


I've also improved validation and added logging in: <br>
[QuoteController.php](src/Controller/QuoteController.php)

Also I've changed route to be more specific:
/api/quotes/v1/calculate

Additionally, I've introduced error codes and fixed status codes in responses.
I’ve used the standard log file to record any errors occurring in the API, along with all request details.
In a real project, I would configure Monolog to log errors to a separate file.

Screenshots from the working API and error handling:

### Success:<br>
![Image](https://github.com/user-attachments/assets/49d6d612-67ee-4b2b-96d3-c38350e3a861)

### Wrong method:<br>
![Image](https://github.com/user-attachments/assets/e53d0823-e0a6-445e-906e-2d0bfb928bd1)

### Wrong format or empty request:<br>
![Image](https://github.com/user-attachments/assets/d1934b77-afc8-4a1e-9af2-58c51197b691)
![Image](https://github.com/user-attachments/assets/6db047b0-0b44-4017-8b85-d2d880bf60da)

### Validation errors:<br>
![Image](https://github.com/user-attachments/assets/c62a5783-834e-4924-9b56-cbadb22fb621)

### Log file:<br>
![Image](https://github.com/user-attachments/assets/c78fa507-2d6e-4c4b-8538-c3a9cf95a88a)