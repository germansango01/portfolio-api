# Portofolio - Rest Api System

This is a Base system developed in **Laravel 12** with a **MySQL** database. Designed to efficiently manage the creation of a standard REST API with testing, best practices and documentation with swagger

### Prerequisites

1. **MySQL Database:** Set up the database and credentials in the `.env` file.
2. **Storage Link:** Run `php artisan storage:link` to create the symbolic link to the public file storage.
3. **Public File Configuration:** Make sure `FILESYSTEM_DISK` is set to `public` in the `.env` file.

### installation

1. **Clone the repository**

    ```bash
    git clone https://gitlab.com/germansango/cursotdd.git
    cd cursotdd
    ```

2. **Install dependencies**

    ```bash
    composer install
    ```

    ```bash
    npm install
    ```

3. **Configure the file `.env`**

    ```bash
    php artisan key:generate
    ```

    Muy Importante Configurar el:

    ```bash
    APP_URL=http://cursotdd.test
    ```

    Change it to your URL

4. **Migrations and Seeders**
   Run the migrations to create the database structure:

    ```bash
    php artisan migrate
    php artisan db:seed
    ```

5. **Storage Link**
   Run the following command to create the symbolic link for the image display to work:

    ```bash
    php artisan storage:link --force
    ```

6. **Start the development server**
    ```bash
    php artisan serve
    ```
    Or access your URL

## About Laravel

Laravel is a web application framework with 

- [Simple, fast routing engine](https://laravel.com/docs/routing).
