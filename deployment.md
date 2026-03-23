# Deployment Guide: ServisBos Platform

This guide provides step-by-step instructions for deploying the **ServisBos** multitenant platform to a Linux-based production server.

---

## 1. Prerequisites

Ensure the following are installed on your server:

- **PHP**: ^8.2 (required extensions: `bcmath`, `curl`, `gd`, `intl`, `mbstring`, `openssl`, `pdo_pgsql`, `xml`, `zip`)
- **Web Server**: Nginx (recommended)
- **Database**: PostgreSQL 15+
- **Composer**: 2.x
- **Node.js**: LTS (18.x or 20.x) & NPM
- **Redis** (optional but recommended for caching and queues)
- **Git**

---

## 2. Server Setup & Repository

Navigate to your web directory and clone the repository:

```bash
cd /var/www
git clone <repository-url> servisbos
cd servisbos
```

Set appropriate directory permissions:

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

---

## 3. Environment Configuration

Copy the example environment file and customize it for production:

```bash
cp .env.example .env
nano .env
```

### Key Configurations for ServisBos Hosting:

```ini
APP_NAME="ServisBos"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://servisbos.id # Central Domain

# Multitenancy Domain Setup
CENTRAL_DOMAIN=servisbos.id

# Database Configuration (PostgreSQL)
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=servisbos_prod
DB_USERNAME=servisbos_admin
DB_PASSWORD=your_secure_password

# Session & Cache (Database/Redis recommended for multitenancy persistence)
SESSION_DRIVER=database
CACHE_STORE=database

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host.com
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="no-reply@servisbos.id"
MAIL_FROM_NAME="${APP_NAME}"
```

---

## 4. Application Installation

Run the installation commands:

```bash
# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Generate security key
php artisan key:generate --force

# Install Node dependencies and build assets
npm install
npm run build
```

---

## 5. Database & Storage Initialization

Prepare the database for production:

```bash
# Run migrations (standard migrations with tenant scoping)
php artisan migrate --force

# Seed initial system data (if required)
# php artisan db:seed --force
```

Link the storage directory to the public folder:

```bash
php artisan storage:link
```

---

## 6. Optimization

For production performance, cache all configuration and routes:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

---

## 7. Nginx Configuration (Wildcard Support)

To support multiple tenants under subdomains, use a wildcard server configuration. Create `/etc/nginx/sites-available/servisbos.conf`:

```nginx
server {
    listen 80;
    listen [::]:80;
    
    # Support central domain and any subdomains for tenants
    server_name servisbos.id *.servisbos.id;
    
    root /var/www/servisbos/public;

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

Enable the site and restart Nginx:
```bash
sudo ln -s /etc/nginx/sites-available/servisbos.conf /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### SSL Setup (Wildcard Cert)
For multitenant subdomain support, a **wildcard SSL certificate** is required:
```bash
sudo certbot certonly --manual -d *.servisbos.id -d servisbos.id --preferred-challenges dns
```

---

## 8. Continuous Background Tasks

### Scheduler (Cron)
Add the following entry to your server's crontab (`crontab -e`):
```bash
* * * * * cd /var/www/servisbos && php artisan schedule:run >> /dev/null 2>&1
```

### Queue Workers (Supervisor)
Create a Supervisor config: `/etc/supervisor/conf.d/servisbos-worker.conf`

```ini
[program:servisbos-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/servisbos/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/servisbos/storage/logs/worker.log
stopwaitsecs=3600
```

Start the workers:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start all
```

---

## 9. Multitenant Onboarding

Once the platform is live:
1. Access the **SuperAdmin Panel** (via central domain).
2. Create your first **Tenant** (e.g., klinbee.servisbos.id).
3. The platform will automatically scope data based on the subdomain or custom domain configured for the tenant.

---

## 10. Troubleshooting

- **Check Logs**: `/var/www/servisbos/storage/logs/laravel.log`
- **Permissions Error**: Ensure `www-data` has write access to `storage` and `bootstrap/cache`.
- **Database Connection**: Verify PostgreSQL connection settings in `.env`.
