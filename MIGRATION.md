# Migration Guide: `kleening-revamp` (Linux -> Windows WSL2 Debian)

This guide is written for an AI agent or developer setting up this project on a **Windows laptop inside WSL2 (Debian)**.

## 1. Repository & Stack Overview
- **Project Folder:** `kleening-revamp`
- **Detected Stack:** Node.js (npm) — Vite, Tailwind CSS, PHP (Composer / Laravel)
- **Git Branch:** `project`
- **Git Remote:** `https://github.com/juliuscesario/kleening-revamp.git`

### Clone & Enter Workspace (WSL2 Debian)
```bash
mkdir -p ~/Projects && cd ~/Projects
git clone https://github.com/juliuscesario/kleening-revamp.git
cd kleening-revamp
git checkout project
```

> **WSL2 Tip:** Always keep the repository inside the Linux filesystem (`~/Projects/...`), NOT under `/mnt/c/...`, for fast file I/O, Docker bind mounts, and reliable file watchers.

## 2. WSL2 Debian Setup & Run Commands
```bash
npm install
composer install
npm run dev  # vite
npm run build  # vite build
php artisan key:generate
php artisan migrate
php artisan serve
```

## 3. Environment Variables (`.env`) Configuration
Secrets are **never** committed to Git. Recreate each `.env` file from its `.env.example` template:

### Directory: `.`
- Detected env files on source machine: `.env`, `.env.example`
- Template file: `.env.example`

```bash
cp .env.example .env
```

| Variable | Example / Placeholder |
|---|---|
| `APP_NAME` | `<YOUR_APP_NAME>` |
| `APP_ENV` | `local` |
| `APP_KEY` | `` |
| `APP_DEBUG` | `true` |
| `APP_URL` | `http://localhost` |
| `APP_LOCALE` | `en` |
| `APP_FALLBACK_LOCALE` | `<YOUR_APP_FALLBACK_LOCALE>` |
| `APP_FAKER_LOCALE` | `<YOUR_APP_FAKER_LOCALE>` |
| `APP_MAINTENANCE_DRIVER` | `<YOUR_APP_MAINTENANCE_DRIVER>` |
| `PHP_CLI_SERVER_WORKERS` | `4` |
| `BCRYPT_ROUNDS` | `12` |
| `LOG_CHANNEL` | `<YOUR_LOG_CHANNEL>` |
| `LOG_STACK` | `<YOUR_LOG_STACK>` |
| `LOG_DEPRECATIONS_CHANNEL` | `<YOUR_LOG_DEPRECATIONS_CHANNEL>` |
| `LOG_LEVEL` | `debug` |
| `DB_CONNECTION` | `sqlite` |
| `SESSION_DRIVER` | `<YOUR_SESSION_DRIVER>` |
| `SESSION_LIFETIME` | `120` |
| `SESSION_ENCRYPT` | `false` |
| `SESSION_PATH` | `<YOUR_SESSION_PATH>` |
| `SESSION_DOMAIN` | `<YOUR_SESSION_DOMAIN>` |
| `BROADCAST_CONNECTION` | `<YOUR_BROADCAST_CONNECTION>` |
| `FILESYSTEM_DISK` | `<YOUR_FILESYSTEM_DISK>` |
| `QUEUE_CONNECTION` | `<YOUR_QUEUE_CONNECTION>` |
| `CACHE_STORE` | `<YOUR_CACHE_STORE>` |
| `MEMCACHED_HOST` | `<YOUR_MEMCACHED_HOST>` |
| `REDIS_CLIENT` | `<YOUR_REDIS_CLIENT>` |
| `REDIS_HOST` | `127.0.0.1` |
| `REDIS_PASSWORD` | `<YOUR_REDIS_PASSWORD>` |
| `REDIS_PORT` | `6379` |
| `MAIL_MAILER` | `<YOUR_MAIL_MAILER>` |
| `MAIL_SCHEME` | `<YOUR_MAIL_SCHEME>` |
| `MAIL_HOST` | `<YOUR_MAIL_HOST>` |
| `MAIL_PORT` | `2525` |
| `MAIL_USERNAME` | `<YOUR_MAIL_USERNAME>` |
| `MAIL_PASSWORD` | `<YOUR_MAIL_PASSWORD>` |
| `MAIL_FROM_ADDRESS` | `<YOUR_MAIL_FROM_ADDRESS>` |
| `MAIL_FROM_NAME` | `<YOUR_MAIL_FROM_NAME>` |
| `AWS_ACCESS_KEY_ID` | `` |
| `AWS_SECRET_ACCESS_KEY` | `` |
| `AWS_DEFAULT_REGION` | `<YOUR_AWS_DEFAULT_REGION>` |
| `AWS_BUCKET` | `` |
| `AWS_USE_PATH_STYLE_ENDPOINT` | `false` |
| `VITE_APP_NAME` | `<YOUR_VITE_APP_NAME>` |
| `HADIRR_BASE_URL` | `<YOUR_HADIRR_BASE_URL>` |
| `HADIRR_ACCESS_KEY` | `` |
| `HADIRR_SECRET_KEY` | `` |
| `HADIRR_BASE_URL` | `<YOUR_HADIRR_BASE_URL>` |
| `HADIRR_ACCESS_KEY` | `<YOUR_HADIRR_ACCESS_KEY>` |
| `HADIRR_SECRET_KEY` | `<YOUR_HADIRR_SECRET_KEY>` |
