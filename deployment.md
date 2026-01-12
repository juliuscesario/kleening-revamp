# Deployment Guide

This guide outlines the steps to deploy the application for a new client (e.g., **klinbee.com**).

## System Requirements
- **PHP**: ^8.2
- **Composer**: Latest version
- **Node.js**: LTS version (Refer to `package.json` engines if specified, otherwise latest LTS is recommended)
- **Database**: Oracle (based on `yajra/laravel-datatables-oracle` dependency) or compatible SQL database supported by Laravel.
- **Web Server**: Nginx or Apache

## 1. Source Code Setup
Clone the repository to the server:
```bash
git clone <repository_url> .
```

## 2. Dependencies Installation
Install PHP and Node.js dependencies:

```bash
# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Install Node.js dependencies
npm install

# Build frontend assets
npm run build
```

## 3. Environment Configuration
Copy the example environment file and configure it for the client.

```bash
cp .env.example .env
```

Edit the `.env` file with the client's specific details:

```ini
APP_NAME="Klinbee"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://klinbee.com

# Database credentials
DB_CONNECTION=oracle
DB_HOST=127.0.0.1
DB_PORT=1521
DB_DATABASE=xe
DB_USERNAME=klinbee_user
DB_PASSWORD=secret

# Other necessary configurations (Mail, Redis, etc.)
```

## 4. Application Initialization
Run the following commands to set up the application keys and storage:

```bash
# Generate application key
php artisan key:generate

# Link storage directory
php artisan storage:link
```

## 5. Database Setup
Run migrations to set up the database schema:

```bash
php artisan migrate --force
```

## 6. Branding & Customization
The application is designed to be dynamically branded.
- **Logo**: Upload the client's logo via **System -> App Settings**.
- **Invoice Settings**: Configure the invoice footer text and details in **System -> App Settings**.

## 7. Web Server Configuration
Point your web server (Nginx/Apache) document root to the `public/` directory.

**Nginx Example Snippet:**
```nginx
server {
    listen 80;
    server_name klinbee.com;
    root /var/www/klinbee.com/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## 8. Post-Deployment Optimization
Once live, optimize the application cache:

```bash
php artisan config:cache
php artisan event:cache
php artisan route:cache
php artisan view:cache
```

## 9. Supervisor (Optional but Recommended)
If the application uses queues, configure Supervisor to run the queue worker:

```bash
php artisan queue:work --tries=3
```
