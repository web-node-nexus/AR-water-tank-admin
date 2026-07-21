# AR Water Tank Cleaners - Admin Panel

Laravel-based admin dashboard for [AR Water Tank Cleaners](https://www.arwatertankcleaners.in/) — booking management, service providers, customers, pricing, reports, and more.

## Requirements

- PHP 8.2+
- Composer
- Node.js 18+
- SQLite (default) or MySQL

## Setup

```bash
cd admin-panel
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install && npm run build
php artisan serve
```

Open: http://localhost:8000/login

## Default Login Credentials

| Role | Email | Password |
|------|-------|----------|
| Super Admin | admin@arwatertankcleaners.in | password |
| Manager | manager@arwatertankcleaners.in | password |

## Features

### Authentication
- Pre-login: branded login page (no public registration)
- Post-login: protected admin routes with session auth
- Role-based access: Super Admin & Manager
- User management (Super Admin only)

### Modules
- **Dashboard** — KPIs, recent bookings, quick actions
- **Bookings** — CRUD, assign provider, cancel, filter/search
- **Customers** — directory with booking history
- **Service Providers** — add/edit, workload, ratings
- **Services & Pricing** — AR website services with pricing slabs
- **Zones** — area/pincode management
- **Photo Gallery** — before/after job photos
- **Payouts** — provider payment tracking
- **Feedback** — ratings & reviews management
- **Leave Management** — approve/reject provider leaves
- **Notifications** — push notification sender (admin UI)
- **Reports** — revenue, job count, provider earnings
- **Settings** — company info, booking slots, policies

## Tech Stack

- Laravel 13
- Laravel Breeze (Blade)
- Tailwind CSS
- SQLite / MySQL

## Project Structure

```
app/
├── Enums/          # UserRole, BookingStatus
├── Http/
│   ├── Controllers/Admin/
│   └── Middleware/   # EnsureUserIsAdmin, EnsureSuperAdmin
├── Models/
└── Services/       # AuditService
resources/views/admin/
```

## MySQL Configuration

Update `.env`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ar_water_tank
DB_USERNAME=root
DB_PASSWORD=
```

Then run `php artisan migrate:fresh --seed`.

## Provider Mobile App API

REST API for the Expo service provider app at `/api/provider/*`.

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

See `../provider-app/README.md` for mobile app setup.

### Test Provider Login
- Phone: `9876543210` (Rahul Sharma)
- OTP logged in Laravel log when `OTP_DRIVER=log`

### Integrations (.env)
| Service | Keys |
|---------|------|
| OTP | `MSG91_*` |
| Virtual Calls | `EXOTEL_*` |
| WhatsApp Photos | `WHATSAPP_*` |
