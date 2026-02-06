
A RESTful API built with Laravel, demonstrating an e-commerce wishlist functionality with token-based authentication.

![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)

## Overview

This Laravel application provides a complete backend API for wishlist management in an e-commerce context. Users can register, authenticate, browse products, and manage their personal wishlist. The API uses Laravel Sanctum for secure token-based authentication and follows RESTful conventions.

## Features

- User registration and authentication with Laravel Sanctum
- Token-based API security
- Product catalog browsing with pagination
- Complete wishlist management (add, view, remove items)
- Idempotent wishlist additions (duplicate prevention)
- Comprehensive validation, error handling and tests

## Requirements

- PHP 8.3 or higher
- Composer
- MySQL 8.0 or higher

## Installation

### 1. Clone the Repository

### 2. Install Dependencies

```bash
composer install
```

### 3. Environment Configuration

Copy the example environment file:

```bash
cp .env.example .env
```

### 4. Database Setup

Create a MySQL database, for example:

```sql
CREATE DATABASE wishlisting;
```

Update your `.env` file with database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=wishlisting
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

<details>
<summary>Alternative: Using SQLite</summary>

If you prefer SQLite for development:

1. Update `.env`:
   ```env
   DB_CONNECTION=sqlite
   ```

2. Create the database file:
   ```bash
   touch database/database.sqlite
   ```
</details>

### 5. Generate Application Key

```bash
php artisan key:generate
```

### 6. Run Migrations and Seeders

```bash
php artisan migrate --seed
```

This will create all necessary tables and seed some sample dental products.

### 7. Start the Development Server

```bash
php artisan serve
```

The API will be available at `http://localhost:8000`.

## Quick Start

### Test Credentials

A test user is automatically created during seeding:

**Email:** `test@example.com`  
**Password:** `password`

You can use these credentials to immediately test the API without registering a new account.

### Basic Workflow

1. **Login** with test credentials to receive an authentication token
2. **Include the token** in the `Authorization: Bearer {token}` header for protected endpoints
3. **Browse products** using the `/api/products` endpoint
4. **Add items to wishlist** using product IDs
5. **View your wishlist** to see all saved items
6. **Remove items** when no longer needed

Use API testing tools like **Postman** or **Insomnia** to interact with the API.

## API Documentation

Base URL: `{{BASE_URL}}`  
(e.g., `http://localhost:8000` when using `php artisan serve`)

All request and response bodies use JSON format.  
Protected endpoints require: `Authorization: Bearer {your_token}`

---

### Authentication

#### Register a New User

**POST** `{{BASE_URL}}/api/register`

Creates a new user account and returns an authentication token.

**Request Body:**
```json
{
    "name": "Sarah Mitchell",
    "email": "sarah@example.com",
    "password": "SecurePass123",
    "password_confirmation": "SecurePass123"
}
```

**Success Response (201 Created):**
```json
{
    "token": "1|abc123xyz789...",
    "user": {
        "id": 2,
        "name": "Dr. Sarah Mitchell",
        "email": "sarah@example.com",
        "created_at": "2026-02-06T10:30:00.000000Z",
        "updated_at": "2026-02-06T10:30:00.000000Z"
    }
}
```

**Validation Error (422 Unprocessable Entity):**
```json
{
    "message": "The email has already been taken. (and 1 more error)",
    "errors": {
        "email": [
            "The email has already been taken."
        ],
        "password": [
            "The password field confirmation does not match."
        ]
    }
}
```

---

#### Login

**POST** `{{BASE_URL}}/api/login`

Authenticates a user and returns an authentication token.

**Request Body:**
```json
{
    "email": "test@example.com",
    "password": "password"
}
```

**Success Response (200 OK):**
```json
{
    "token": "2|def456uvw789...",
    "user": {
        "id": 1,
        "name": "Test User",
        "email": "test@example.com",
        "created_at": "2026-02-05T14:20:00.000000Z",
        "updated_at": "2026-02-05T14:20:00.000000Z"
    }
}
```

**Authentication Error (422 Unprocessable Entity):**
```json
{
    "message": "The provided credentials are incorrect.",
    "errors": {
        "email": [
            "The provided credentials are incorrect."
        ]
    }
}
```

---

#### Logout

**POST** `{{BASE_URL}}/api/logout`

Revokes the current authentication token.

**Headers:**
```
Authorization: Bearer {your_token}
```

**Success Response (204 No Content):**
```
(Empty response body)
```

**Unauthenticated Error (401 Unauthorized):**
```json
{
    "message": "Unauthenticated."
}
```

---

### Products

#### List All Products

**GET** `{{BASE_URL}}/api/products`

Returns a paginated list of available products.

**Headers:**
```
Authorization: Bearer {your_token}
```

**Success Response (200 OK):**
```json
{
    "data": [
        {
            "id": 1,
            "name": "Glidewell BruxZir Crown",
            "description": "Full-contour zirconia crown with exceptional strength and aesthetics",
            "price": "450.00",
            "created_at": "2026-02-05T14:20:00.000000Z",
            "updated_at": "2026-02-05T14:20:00.000000Z"
        },
        {
            "id": 2,
            "name": "Ivoclar IPS e.max Veneer",
            "description": "Lithium disilicate ceramic veneer for superior esthetics",
            "price": "320.00",
            "created_at": "2026-02-05T14:20:00.000000Z",
            "updated_at": "2026-02-05T14:20:00.000000Z"
        },
        {
            "id": 3,
            "name": "3M Filtek Supreme Composite",
            "description": "Universal nano-hybrid composite restorative material",
            "price": "85.50",
            "created_at": "2026-02-05T14:20:00.000000Z",
            "updated_at": "2026-02-05T14:20:00.000000Z"
        }
    ],
    "links": {
        "first": "{{BASE_URL}}/api/products?page=1",
        "last": "{{BASE_URL}}/api/products?page=2",
        "prev": null,
        "next": "{{BASE_URL}}/api/products?page=2"
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 2,
        "path": "{{BASE_URL}}/api/products",
        "per_page": 15,
        "to": 15,
        "total": 25
    }
}
```

**Unauthenticated Error (401 Unauthorized):**
```json
{
    "message": "Unauthenticated."
}
```

---

### Wishlist

#### Get User's Wishlist

**GET** `{{BASE_URL}}/api/wishlist`

Returns all items in the authenticated user's wishlist.

**Headers:**
```
Authorization: Bearer {your_token}
```

**Success Response (200 OK):**
```json
{
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "product_id": 1,
            "created_at": "2026-02-06T10:45:00.000000Z",
            "updated_at": "2026-02-06T10:45:00.000000Z",
            "product": {
                "id": 1,
                "name": "Glidewell BruxZir Crown",
                "description": "Full-contour zirconia crown with exceptional strength and aesthetics",
                "price": "450.00",
                "created_at": "2026-02-05T14:20:00.000000Z",
                "updated_at": "2026-02-05T14:20:00.000000Z"
            }
        },
        {
            "id": 2,
            "user_id": 1,
            "product_id": 3,
            "created_at": "2026-02-06T10:47:00.000000Z",
            "updated_at": "2026-02-06T10:47:00.000000Z",
            "product": {
                "id": 3,
                "name": "3M Filtek Supreme Composite",
                "description": "Universal nano-hybrid composite restorative material",
                "price": "85.50",
                "created_at": "2026-02-05T14:20:00.000000Z",
                "updated_at": "2026-02-05T14:20:00.000000Z"
            }
        }
    ]
}
```

**Unauthenticated Error (401 Unauthorized):**
```json
{
    "message": "Unauthenticated."
}
```

---

#### Add Item to Wishlist

**POST** `{{BASE_URL}}/api/wishlist`

Adds a product to the user's wishlist. This operation is idempotent - adding the same product multiple times will not create duplicates.

**Headers:**
```
Authorization: Bearer {your_token}
```

**Request Body:**
```json
{
    "product_id": 5
}
```

**Success Response (201 Created):**
```json
{
    "data": {
        "id": 3,
        "user_id": 1,
        "product_id": 5,
        "created_at": "2026-02-06T11:00:00.000000Z",
        "updated_at": "2026-02-06T11:00:00.000000Z",
        "product": {
            "id": 5,
            "name": "Dentsply Sirona Ceramir",
            "description": "Bioactive restorative material with fluoride release",
            "price": "125.00",
            "created_at": "2026-02-05T14:20:00.000000Z",
            "updated_at": "2026-02-05T14:20:00.000000Z"
        }
    }
}
```

**Validation Error (422 Unprocessable Entity):**
```json
{
    "message": "The product id field is required.",
    "errors": {
        "product_id": [
            "The product id field is required."
        ]
    }
}
```

**Product Not Found (422 Unprocessable Entity):**
```json
{
    "message": "The selected product id is invalid.",
    "errors": {
        "product_id": [
            "The selected product id is invalid."
        ]
    }
}
```

**Unauthenticated Error (401 Unauthorized):**
```json
{
    "message": "Unauthenticated."
}
```

---

#### Remove Item from Wishlist

**DELETE** `{{BASE_URL}}/api/wishlist/{id}`

Removes a specific item from the user's wishlist. Users can only remove their own wishlist items.

**Headers:**
```
Authorization: Bearer {your_token}
```

**Success Response (204 No Content):**
```
(Empty response body)
```

**Not Found (404 Not Found):**
```json
{
    "message": "Wishlist item not found."
}
```

**Unauthenticated Error (401 Unauthorized):**
```json
{
    "message": "Unauthenticated."
}
```
## Architecture

This application follows Laravel best practices with a clean, maintainable architecture:

### Actions Pattern
Business logic is encapsulated in dedicated Action classes for reusability and testability:

### API Resources
All API responses use Laravel's API Resource classes for consistent JSON transformation:

### Design Decisions
- **No Repository Pattern** - Direct Eloquent usage for simplicity and Laravel convention
- **Token Authentication** - Laravel Sanctum for stateless API security
- **Eager Loading** - Prevents N+1 query problems throughout the application
- **Idempotent Operations** - Adding duplicate wishlist items returns success without creating duplicates

## Testing

This application includes comprehensive test coverage.

### Run All Tests
```bash
php artisan test
```

## Troubleshooting

### Port Already in Use
```
Failed to listen on 127.0.0.1:8000
```
**Solution:** Use a different port:
```bash
php artisan serve --port=8001
```

## Code Style

This project uses [Laravel Pint](https://laravel.com/docs/pint) for code formatting. To format your code:

```bash
./vendor/bin/pint
```

---

Built with Laravel 12 - The PHP Framework for Web Artisans
