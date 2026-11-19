

# DevIt | Stocki

An internal web application that helps DevIt manage development projects, client payments, maintenance contracts, and Stocki software subscriptions in one centralized platform.

## Features

- **Dashboard & Notifications:** Track monthly revenue, outstanding payments, upcoming deadlines, and project renewal reminders.
- **Project Management:** Create and manage client projects, track costs, advances, payment installments, renewal dates, and technical access credentials.
- **Payment Management:** Monitor project installments, payment statuses, outstanding balances, and payment history.
- **Maintenance & Subscriptions:** Manage monthly maintenance contracts, Stocki subscriptions (monthly, quarterly, and yearly), and lifetime licenses.
- **Client Management:** Maintain client information and access complete financial histories, including payments received and outstanding balances.
- **User Management & Statistics:** Manage user accounts with administrator-based permissions and analyze revenue through monthly, quarterly, and yearly statistics.

## Tech Stack

**Backend:**
- PHP 8.4+
- PDO
- Composer
- PHP dotenv

**Database:**
- MySQL 8+

**Frontend:**
- HTML5
- CSS3
- JavaScript
- Bootstrap 5
- DataTables
- Select2
- ApexCharts
- FormValidation

**Server & Security:**
- Apache
- Apache mod_rewrite
- PHP Sessions Authentication
- Password Hashing (password_hash / password_verify)
- AES-256-GCM Encryption
- PDO Prepared Statements

## Screenshots

| Login | Dashboard |
|---|---|
| ![Login](screenshots/login.png) | ![Dashboard](screenshots/dashboard.png) |

| Project Management | Statistics |
|---|---|
| ![Projects](screenshots/projects.png) | ![Statistics](screenshots/statistics.png) |

## Getting Started

### Prerequisites

- PHP 8.4 or higher
- MySQL 8 or recent MariaDB
- Apache with mod_rewrite enabled
- Composer
- PHP extensions: pdo_mysql, mbstring, openssl

### 1. Clone the repository

```bash
git clone <REPOSITORY_URL> devitstocki
cd devitstocki
```

### 2. Install dependencies

```
composer install --no-dev
```

### 3. Configure environment variables

```
cp .env.example .env
```

Configure your `.env` file:

```
DB_HOST=localhost
DB_USER=devitstocki
DB_PASS=your_database_password
DB_NAME=db_devitstocki
APP_KEY=your_base64_encryption_key
```

Generate a new encryption key:

```
php -r "echo base64_encode(random_bytes(32)), PHP_EOL;"
```

**Important:** Keep your `APP_KEY` safe. It is required to decrypt project technical credentials.

### 4. Set up the database

Create the database:

```
CREATE DATABASE db_devitstocki
CHARACTER SET utf8mb4
COLLATE utf8mb4_0900_ai_ci;

CREATE USER 'devitstocki'@'localhost'
IDENTIFIED BY 'your_database_password';

GRANT ALL PRIVILEGES ON db_devitstocki.*
TO 'devitstocki'@'localhost';
```

Import the database:

```
mysql -u devitstocki -p db_devitstocki < database/db_devitstocki.sql
```

The provided SQL dump already includes the database structure and applied migrations.

### 5. Configure Apache and start the application

Place the project inside your Apache document root:

```
/var/www/html/devitstocki/
```

Enable Apache rewrite:

```
sudo a2enmod rewrite
sudo systemctl restart apache2
```

Set permissions for application logs:

```
sudo chown -R www-data:www-data logs/
```

Access the application:

```
http://localhost/html/devitstocki/public/
```

**Note:** The application expects a specific URL path. If you use a different deployment path, update the corresponding configuration in `app/Router/Router.php`.

## Test Accounts

Demo credentials should be added here when available.

Role

Email

Password

Administrator

`<ADMIN_EMAIL>`

`<ADMIN_PASSWORD>`

User

`<USER_EMAIL>`

`<USER_PASSWORD>`

_Demo accounts only. Never publish production credentials._

## Author

**Mohamed Ali Bettaieb**

Backend Developer | PHP & Software Engineering

-   LinkedIn: [Mohamed Ali Bettaieb](https://www.linkedin.com/in/mohamed-ali-bettaieb/)
-   GitHub: [Dalikusjr](https://github.com/Dalikusjr)



**Note:** The README reflects the actual project architecture documented in DevIt | Stocki. It does not claim Symfony, Laravel, Angular or JWT integration.

