# Kleening.id — Tech Stack

## Core

| Layer | Technology | Version | Notes |
|-------|-----------|---------|-------|
| **Framework** | Laravel | ^12.0 | |
| **Language** | PHP | ^8.2 | |
| **Database** | MySQL | | 39 migrations |
| **Auth (Web)** | Laravel Breeze | ^2.3 | Blade scaffolding, phone_number login |
| **Auth (API)** | Laravel Sanctum | ^4.2 | Token-based authentication |

## Frontend

| Technology | Version | Purpose |
|-----------|---------|---------|
| **Blade** | — | Server-side templating engine |
| **Tailwind CSS** | ^3.1.0 | Utility-first CSS framework |
| **@tailwindcss/forms** | ^0.5.2 | Form styling plugin |
| **@tailwindcss/vite** | ^4.0.0 | Vite integration plugin |
| **Tabler UI** | ^1.4.0 | Admin UI framework (via `@tabler/core`) |
| **Alpine.js** | ^3.4.2 | Lightweight JS reactivity |
| **Vite** | ^7.0.4 | Asset bundler |
| **jQuery** | ^3.7.1 | Globally loaded (DataTables dependency) |
| **PostCSS** | ^8.4.31 | CSS processing |
| **Autoprefixer** | ^10.4.2 | CSS vendor prefixing |

## JavaScript Libraries

| Library | Version | Purpose |
|---------|---------|---------|
| **DataTables.net BS5** | ^2.3.4 | Server-side data tables |
| **DataTables.net Responsive BS5** | ^3.0.6 | Responsive table plugin |
| **ApexCharts** | ^5.3.5 | Charts for analytics reports |
| **Select2** | ^4.1.0-rc.0 | Enhanced searchable dropdowns |
| **SweetAlert2** | ^11.23.0 | Modals and toast notifications |
| **Toastr** | ^2.1.4 | Toast notifications |
| **Axios** | ^1.11.0 | devDep — **NOT used in app** (use `fetch()`) |
| **Concurrently** | ^9.0.1 | Parallel dev process runner |

## PHP Packages (Production)

| Package | Version | Purpose |
|---------|---------|---------|
| **laravel/framework** | ^12.0 | Core framework |
| **laravel/sanctum** | ^4.2 | API token authentication |
| **laravel/tinker** | ^2.10.1 | REPL for artisan |
| **barryvdh/laravel-dompdf** | ^3.1 | PDF generation (invoices, SO print) |
| **spatie/image** | ^3.8 | Image manipulation (work photo processing) |
| **yajra/laravel-datatables-oracle** | ^12.4 | Server-side DataTables processing |

## PHP Packages (Development)

| Package | Version | Purpose |
|---------|---------|---------|
| **laravel/breeze** | ^2.3 | Auth scaffolding |
| **laravel/pint** | ^1.24 | Code style fixer (PSR-12) |
| **laravel/sail** | ^1.41 | Docker development environment |
| **laravel/pail** | ^1.2.2 | Real-time log tailing |
| **phpunit/phpunit** | ^11.5.3 | Unit/Feature testing |
| **mockery/mockery** | ^1.6 | Mocking framework |
| **fakerphp/faker** | ^1.23 | Test data generation |
| **nunomaduro/collision** | ^8.6 | Error reporting for CLI |
| **knuckleswtf/scribe** | ^5.9 | API documentation generator |

## Dev Tools Summary

| Tool | Purpose |
|------|---------|
| **Laravel Pint** | Code style enforcement (PSR-12) |
| **PHPUnit** | ^11.5.3 — Unit and feature testing |
| **Laravel Sail** | Docker-based development environment |
| **Laravel Pail** | Real-time log streaming |
| **Concurrently** | Parallel dev processes (server, queue, logs, vite) |
| **Scribe** | Auto-generates API documentation at `/docs` |

## Build & Dev Commands

```bash
# Install dependencies
composer install
npm install

# Dev (runs all in parallel via composer.json script)
composer run dev
# → php artisan serve
# → php artisan queue:listen --tries=1
# → php artisan pail --timeout=0
# → npm run dev (vite)

# Build for production
npm run build

# Run tests
composer run test
# → php artisan config:clear
# → php artisan test

# Generate API docs
php artisan scribe:generate
```

## Vite Configuration

```js
// vite.config.js
plugins: [
    laravel({
        input: ['resources/css/app.css', 'resources/js/app.js'],
        refresh: true,
    }),
],
```

## Tailwind Configuration

```js
// tailwind.config.js
content: [
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    './storage/framework/views/*.php',
    './resources/views/**/*.blade.php',
],
theme: {
    extend: {
        fontFamily: {
            sans: ['Figtree', ...defaultTheme.fontFamily.sans],
        },
    },
},
plugins: [forms],
```

## Config Files

| File | Purpose |
|------|---------|
| `tailwind.config.js` | Tailwind + forms plugin, Figtree font |
| `postcss.config.js` | PostCSS autoprefixer |
| `vite.config.js` | Laravel Vite plugin with hot reload |
| `config/datatables.php` | Yajra DataTables configuration |
| `config/scribe.php` | Scribe API docs configuration |
| `.editorconfig` | Editor settings: 4-space indent, LF line endings, UTF-8 |

## Code Style

- **Indentation**: 4 spaces (from `.editorconfig`)
- **Line endings**: LF (Unix)
- **Charset**: UTF-8
- **Trailing whitespace**: Trimmed on save
- **PHP style**: PSR-12 (via Laravel Pint)
- **YAML**: 2-space indent
