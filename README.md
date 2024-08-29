# Customer API

## Overview

This project is an API-based application built using Laravel 10 and Doctrine ORM, leveraging the [Laravel Doctrine ORM package](https://github.com/laravel-doctrine/orm). It imports customer data from an external API, [Random User API](https://randomuser.me/), and provides RESTful endpoints to list and retrieve detailed customer information. 

## Prerequisites

- PHP 8.1.0 or greater
- Composer
- MySQL 5.7+ or compatible database

## Setup Instructions

### 1. Clone the Repository
```
git clone https://github.com/MarkBoGum/customer-api.git
cd customer-api
```

### 2. Environment Configuration
```
cp .env.example .env
```
- Update the `.env` file with your database credentials and other necessary configurations.

### 3. Install Dependencies
```
composer install
```

### 4. Doctrine Setup

Run the following commands to create the database schema:

```
php artisan doctrine:schema:create
```

### 5. Running the Application

Start the Laravel development server:

```
php artisan serve
```

The application should now be running at `http://localhost:8000`.


## Data Import Process

### Using the Command-Line Tool

You can import customer data by running the provided Artisan command:

```
php artisan import:customers <number-of-customers>
```

- Replace `<number-of-customers>` with the number of customers you want to import.
- The default value is 100, so if you don't specify a number, the command will import 100 customers by default.
- This command will fetch data from the external API and store it in the database.

## API Endpoints Documentation

### 1. List Customers
**URL:** `/api/customers`  
**Method:** `GET`

**Query Parameters:**
- `page`  (optional, default = 1) 

    *The page number to retrieve.*
- `pageSize` (optional, default = 10, max=100)

    *Number of records per page.*

**Example Request:**
```
GET /api/customers?page=1&pageSize=2
```

**Example Response:**
```json
{
    "status": "success",
    "data": [
        {
            "full_name": "John Doe",
            "email": "john.doe@example.com",
            "country": "Australia"
        },
        {
            "full_name": "Allen Harper",
            "email": "allen.harper@example.com",
            "country": "Australia"
        },
    ]
}
```

### 2. Retrieve Customer Details
**URL:** `/api/customers/{id}`  
**Method:** `GET`

**Path Parameters:**
- `id` : *The ID of the customer to retrieve.*

**Example Request:**
```
GET /api/customers/1
```

**Example Response:**
```json
{
    "status": "success",
    "data": {
        "full_name": "John Doe",
        "email": "john.doe@example.com",
        "username": "johndoe",
        "gender": "male",
        "country": "Australia",
        "city": "Sydney",
        "phone": "1234567890"
    }
}
```

### 3. Error Responses

Example:
```json
{
    "message": "Customer not found"
}
```

## Running Tests

### 1. Setup a Test Database

Before running tests, ensure you have a separate database created specifically for testing purposes. This prevents test data from interfering with your development or production databases.

### 2. Configurations for the Test Database

Make sure your `phpunit.xml` file is properly configured to use the test database. For example:

```xml
<php>
    <env name="DB_CONNECTION" value="mysql"/>
    <env name="DB_DATABASE" value="your_test_database"/>
    <env name="DB_USERNAME" value="your_test_username"/>
    <env name="DB_PASSWORD" value="your_test_password"/>
</php>
```

### 3. Run Tests
Run the following commands to execute all unit and feature tests:

```
php artisan test
```
