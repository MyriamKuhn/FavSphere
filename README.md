
# FavSphere

FavSphere is a personal bookmarking application created to help users organize, categorize, and annotate their favorite links in one accessible space. Designed with simplicity and security in mind, FavSphere offers a user-friendly interface and a robust backend to manage personal or family-curated resources with ease. This project is exclusively for personal use.

The application is a web-based solution running in a WAMPP environment to support a PHP/Apache setup. It includes HTML, CSS, JavaScript, and PHP files, with dependencies like Dotenv, PHP-JWT, PHPUnit and Bootstrap. The backend is powered by the FavSphere API, which is fully documented using Swagger.
## Requirements
Before you start, make sure you have the following components installed on your machine:
- [PHP 8.3](https://www.php.net/docs.php)
- [Composer](https://getcomposer.org/)
- A web server (Apache, Nginx, etc.)
- A MySQL or MariaDB database (at least version 10.6) to store the application data
## Database Setup

### Create a MySQL database:
You will need to create a MySQL or MariaDB database for the project. You can do this by running the following SQL command in your MySQL client:

```bash
CREATE DATABASE favsphere;

CREATE TABLE user (
  id INT(11) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  user VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL
) 
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE category (
  id INT(11) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  color VARCHAR(7) NOT NULL,
  fk_user_id INT(11) UNSIGNED NOT NULL,
  FOREIGN KEY(fk_user_id) REFERENCES user(id) ON DELETE CASCADE ON UPDATE CASCADE
) 
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE link (
  id INT(11) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  url VARCHAR(255) NOT NULL,
  title VARCHAR(100) NOT NULL,
  description TEXT NOT NULL,
  fk_category_id INT(11) UNSIGNED NOT NULL,
  fk_user_id INT(11) UNSIGNED NOT NULL,
  FOREIGN KEY(fk_category_id) REFERENCES category(id) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY(fk_user_id) REFERENCES user(id) ON DELETE CASCADE ON UPDATE CASCADE
) 
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

### Configure your database credentials:
Add your database information to the .env file (located in the App directory). Ensure the following variables are set:

`DB_HOST`=localhost

`DB_NAME`=favsphere

`DB_USER`=your_db_user

`DB_PASSWORD`=your_db_password


### Initialize the database:
Once the database is created, you will need to initialize it with the required tables. You can either manually create them by running the SQL queries provided above, or use migration scripts if they are available (migration steps could be added in the future).

### Create at least one user:
To use the application, create at least one user in the user table. You can do this manually through your MySQL client or by running an SQL script. Example:

```bash
INSERT INTO users (username, password) VALUES ('admin', 'your_secure_password');
```

The password must be hashed using BCrypt. I recommend using this website to generate the encrypted password and then setting the encrypted password in the database: [https://bcrypt-generator.com/](https://bcrypt-generator.com/)

### Generate JWT Token:
After creating the user, you can generate a JWT token for authentication using the credentials. This token can be used in Swagger or any other tool for API testing.
    
## Environment Variables

To run this project, you will need to add the following environment variables:

- For the main application: Create a .env file and place it in the root of the App directory with the following variables:

`DB_HOST` – The host of the database.

`DB_NAME` – The name of the database used by FavSphere.

`DB_USER` – The username for connecting to the database.

`DB_PASSWORD` – The password associated with the database user.

`JWT_SECRET` – A secret key for signing and verifying JWT tokens (recommended to be a long, random string for security).

- For testing: Create a .env.test file at the root of the project with the following variables:

`JWT_SECRET` – A secret key for signing and verifying JWT tokens (recommended to be a long, random string for security).

`DATABASE_URL` – The complete database URL, e.g., mysql:host=`HOSTNAME`;dbname=`DB-NAME`,`DB-USER`,`DB-PASSWORD`.

`USER_NAME` – The username used to connect to FavSphere, stored in the database.

`USER_PASSWORD` – The password used to connect to FavSphere, stored in the database.

`TOKEN` – – A pre-defined JWT token for testing, generated using the USER_NAME and USER_PASSWORD along with the JWT_SECRET. This token can be directly provided in Swagger for testing purposes.

## To run Locally

Clone the project

```bash
  git clone https://github.com/MyriamKuhn/FavSphere.git
```

Go to the project directory

```bash
  cd FavSphere
```

If composer.json is already configured with the required dependencies, run:
```bash
  composer install
```

Otherwise, initialize Composer and add each dependency manually:
```bash
  composer init
  composer require firebase/php-jwt
  composer require vlucas/phpdotenv
  composer require --dev phpunit/phpunit
```
Set your web server to point to the public directory of the project.
Start your web server and navigate to your local instance of the application.

## Troubleshooting

### Environment Variables Not Loading
- Problem: Environment variables in .env or .env.test aren’t being recognized.
- Solution: Make sure the .env files are in the correct directories (App for .env and root for .env.test) and that they are properly formatted (e.g., no extra spaces around =). Also, check that vlucas/phpdotenv is properly installed.

### Database Connection Issues
- Problem: Unable to connect to the database; getting connection errors.
- Solution: Verify that DB_HOST, DB_NAME, DB_USER, and DB_PASSWORD are correctly set in your .env file. Make sure your database server is running and accessible, and that the user has appropriate permissions.

### JWT Token Errors
- Problem: Errors related to JWT tokens, such as "invalid token" or "token expired."
- Solution: Ensure that JWT_SECRET in your .env and .env.test files is correctly set. If testing with a predefined token, verify that the token was generated using the correct secret and user credentials.

### Composer Dependency Issues
- Problem: Errors when running Composer commands, or dependencies not found.
- Solution: Run composer install to ensure all dependencies are installed. If issues persist, try clearing Composer's cache with composer clear-cache, then re-run composer install.

### Testing Issues
- Problem: Tests fail or .env.test variables are not loaded.
- Solution: Ensure that the .env.test file is correctly configured and in the project root. If you’re using PHPUnit, make sure phpunit/phpunit is installed and correctly set up.

### Swagger Documentation Not Loading
- Problem: Swagger UI does not display or fails to load.
- Solution: Verify that all required endpoints are accessible and that the Swagger setup is correctly configured in your API. Ensure your server is running and reachable at the specified address.
## Dependencies

- [PHP 8.3](https://www.php.net/docs.php) - The backend language for building the application.
- [PHP-JWT](https://github.com/firebase/php-jwt) - A library to encode and decode JSON Web Tokens (JWT) in PHP, used for secure user authentication and authorization.
- [Dotenv 5.6](https://github.com/vlucas/phpdotenv) - A library for managing environment variables, which allows the configuration of sensitive data (like database credentials) to be stored securely in a .env file.
- [Bootstrap 5.3](https://getbootstrap.com/docs/5.3/getting-started/introduction/) - A popular CSS framework that helps with building responsive and mobile-first web pages, ensuring a consistent design across various screen sizes.
## Deployed Application
You can access the deployed version of the FavSphere application by visiting the following link:

[FavSphere Application](https://favsphere.myriamkuhn.com/)

This is the live web application where users can log in, organize their links, and manage categories.

## Deployed Swagger Documentation
The Swagger UI for the FavSphere API is also deployed, allowing you to test the API endpoints interactively:

[FavSphere Swagger API Documentation](https://favsphere.myriamkuhn.com/app/)

Here you can explore the API documentation, test requests, and see the responses from the live backend.

## Feedback

I value your feedback! If you have any suggestions, questions, or issues regarding the FavSphere project, please feel free to reach out:

myriam.kuehn@free.fr

Your input helps improve the project and ensure a better user experience.
## License

This project is licensed by MIT.

