# Installation Guide - Approval System Backend

This guide will help you set up the Approval System backend project on your local machine.

## Prerequisites

Before you begin, ensure you have the following installed on your system:
- **PHP**: `^8.2` or higher
- **Composer**: Package manager for PHP
- **Node.js & npm**: (Optional but recommended if Vite/frontend assets are used in the Laravel app)
- **PostgreSQL**: The project is pre-configured to use PostgreSQL (`pgsql`)
- **Git**: For version control

---

## Step-by-Step Installation

### 1. Clone the Repository
Clone the project repository to your local machine and navigate into the directory.
```bash
git clone <your-repo-url>
cd approval-sistem-be
```

### 2. Install PHP Dependencies
Run Composer to install all the required PHP packages defined in `composer.json`.
```bash
composer install
```

### 3. Setup Environment Variables
Copy the example environment file to create your local `.env` file.
```bash
cp .env.example .env
# On Windows Command Prompt you can use: copy .env.example .env
```

### 4. Configure the Database
Open the newly created `.env` file in your code editor and update the database configuration to match your local PostgreSQL setup.

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=approval_sistem_be  # Make sure this database is created in PostgreSQL
DB_USERNAME=root                 # Your PostgreSQL username
DB_PASSWORD=your_password        # Your PostgreSQL password
```
*Note: Make sure you have created an empty database named `approval_sistem_be` (or your chosen name) in pgAdmin or via the PostgreSQL CLI before proceeding.*

### 5. Generate Application Key
Generate a unique application key. This sets the `APP_KEY` value in your `.env` file.
```bash
php artisan key:generate
```

### 6. Run Database Migrations
Run the migrations to create the necessary tables in your database.
```bash
php artisan migrate
```

### 7. Run Database Seeders (Crucial for RBAC)
To populate the database with essential data like Roles (User, Verifier, Approver) and initial admin/test accounts, run the seeders:
```bash
php artisan db:seed
```

### 8. Link Storage (Optional but Recommended)
If your application handles file uploads (like attachment uploads for claims), create a symbolic link from `public/storage` to `storage/app/public`.
```bash
php artisan storage:link
```

### 9. Run the Local Development Server
Finally, start the Laravel development server.
```bash
php artisan serve
```
Your backend API should now be running at `http://localhost:8000`.

---

## Quick Setup Command (Alternative)
If you want to run the automated setup commands defined in the `composer.json`, you can simply run:
```bash
composer run-script setup
```
*Note: You still need to make sure your database is created and configured in the `.env` file between the copy `.env` and `migrate` steps if the script fails, or configure the `.env` manually first.*

---

## Testing the API
You can now test the API endpoints using tools like Postman, Insomnia, or cURL.
Start by hitting the login endpoint:
- **POST** `http://localhost:8000/api/login`

Refer to the [API_DOCS.md](./API_DOCS.md) for detailed information on available endpoints and required payloads.
