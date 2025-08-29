# Blog REST API

API RESTful desarrollada con Laravel 12 y PHP 8.2 para gestionar el contenido de un blog o portafolio. Proporciona endpoints para la gestión de posts, categorías, tags y autenticación de usuarios mediante Laravel Passport.

## Características

-   **Autenticación:** Sistema de registro y login basado en tokens con Laravel Passport.
-   **Gestión de Posts:** Endpoints para listar, buscar y filtrar posts.
-   **Taxonomía:** Filtrado de posts por categoría, tag y autor.
-   **Documentación de API:** Generada automáticamente con `l5-swagger`.
-   **Buenas Prácticas:** Estructura de proyecto organizada, siguiendo las convenciones de Laravel.

---

## Instalación

Sigue estos pasos para configurar el entorno de desarrollo local.

### Prerrequisitos

-   PHP 8.2 o superior
-   Composer
-   Un servidor de base de datos (ej. MySQL, MariaDB)
-   Node.js y NPM (opcional, para el frontend)

### Pasos

1.  **Clonar el repositorio**

    ```bash
    git clone <tu-repositorio-git> portfolio-api
    cd portfolio-api
    ```

2.  **Instalar dependencias**

    ```bash
    composer install
    npm install
    ```

3.  **Configurar el entorno**
    Copia el archivo de ejemplo `.env.example` y configúralo según tus necesidades.

    ```bash
    copy .env.example .env
    ```

    Genera la clave de la aplicación:

    ```bash
    php artisan key:generate
    ```

    Asegúrate de configurar correctamente la conexión a la base de datos (`DB_*`) y la URL de la aplicación (`APP_URL`) en tu archivo `.env`.

4.  **Instalar Laravel Passport**
    Este comando creará las claves de encriptación necesarias para generar los tokens de acceso.

    ```bash
    php artisan passport:install
    ```

5.  **Ejecutar las migraciones y seeders**
    Esto creará la estructura de la base de datos y la llenará con datos de ejemplo.

    ```bash
    php artisan migrate --seed
    ```

6.  **Crear el enlace simbólico de almacenamiento**

    ```bash
    php artisan storage:link
    ```

7.  **Iniciar el servidor**
    Puedes usar el servidor de desarrollo de Artisan:
    ```bash
    php artisan serve
    ```
    La API estará disponible en `http://127.0.0.1:8000`.

---

## API Endpoints

A continuación se detallan los endpoints disponibles en la API.

### Documentación Interactiva

Para una documentación completa y actualizada, puedes visitar la ruta generada por Swagger una vez que el servidor esté en funcionamiento:

-   **URL:** [`/api/documentation`](/api/documentation)

### Autenticación

| Método | URI              | Nombre     | Descripción                            |
| :----- | :--------------- | :--------- | :------------------------------------- |
| `POST` | `/api/register`  | `register` | Registra un nuevo usuario.             |
| `POST` | `/api/login`     | `login`    | Inicia sesión y obtiene un token.      |
| `POST` | `/api/logout`    | `logout`   | Cierra la sesión del usuario (Requiere Auth). |
| `GET`  | `/api/user`      | `user`     | Obtiene la información del usuario autenticado. |

### Posts

Todas las rutas de posts (excepto `resume`) requieren autenticación.

| Método | URI                                  | Nombre            | Descripción                                            |
| :----- | :----------------------------------- | :---------------- | :----------------------------------------------------- |
| `GET`  | `/api/resume`                        | `resume`          | Obtiene un resumen con los últimos posts y más vistos. |
| `GET`  | `/api/posts`                         | `posts`           | Obtiene una lista paginada de todos los posts.         |
| `GET`  | `/api/search`                        | `search`          | Busca posts por término, categoría, autor o tag.       |
| `GET`  | `/api/posts/category/{category:slug}`| `postsByCategory` | Obtiene los posts de una categoría específica.         |
| `GET`  | `/api/posts/tag/{tag:slug}`          | `postsByTag`      | Obtiene los posts asociados a un tag específico.       |
| `GET`  | `/api/posts/user/{user}`             | `postsByUser`     | Obtiene los posts de un usuario específico.            |

**Parámetros de paginación y búsqueda (Query Params):**

-   `page` (int): Número de página a obtener.
-   `per_page` (int): Número de resultados por página.
-   `q` (string): Término de búsqueda para el endpoint `/api/search`.
-   `category` (int): ID de la categoría para filtrar en `/api/search`.
-   `tag` (int): ID del tag para filtrar en `/api/search`.
-   `author` (int): ID del autor para filtrar en `/api/search`.