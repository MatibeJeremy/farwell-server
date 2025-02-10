# Farwell Application

## Overview
Farwell is a web application built with Laravel, providing user authentication, profile management, and employee data management functionalities. The application also includes Swagger documentation for its API endpoints.

## Technologies Used
- PHP
- Laravel
- Composer
- MySQL

## Setup Instructions

### Prerequisites
- PHP >= 8.0
- Composer
- NPM
- MySQL

### Installation

1. **Clone the repository:**
    ```sh
    git clone https://github.com/MatibeJeremy/farwell.git
    cd farwell
    ```

2. **Install PHP dependencies:**
    ```sh
    composer install
    ```

3. **Copy the `.env` file and configure your environment variables:**
    ```sh
    cp .env.example .env
    ```

4. **Generate the application key:**
    ```sh
    php artisan key:generate
    ```

5. **Set up the database:**
    - Create a MySQL database.
    - Update the `.env` file with your database credentials.
    - Run the migrations:
        ```sh
        php artisan migrate
        ```

6. **Run the application:**
    ```sh
    php artisan serve
    ```

## Swagger Documentation

### Generating Swagger Docs

1. **Install the Swagger package:**
    ```sh
    composer require "darkaonline/l5-swagger"
    ```

2. **Publish the Swagger configuration:**
    ```sh
    php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"
    ```

3. **Generate the Swagger documentation:**
    ```sh
    php artisan l5-swagger:generate
    ```

4. **Access the Swagger UI:**
    - Visit `http://localhost/api/documentation` in your browser.

## API Endpoints

### Auth
- `POST /register` - Register a new user
- `POST /login` - Login a user
- `GET /activate/{token}` - Activate a user account

### User
- `GET /user` - Get the authenticated user
- `PUT /user/update` - Update user profile
- `POST /user/upload` - Upload and update profile picture
- `POST /user/password` - Change user password

### Employee
- `POST /upload` - Upload and process employee data file
- `GET /employees` - Retrieve employee data

## License
This project is licensed under the MIT License. See the `LICENSE` file for details.