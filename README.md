# SmartBiz

SmartBiz is a modular retail / POS admin application built on **Laravel 12**, **nwidart/laravel-modules**, and the **TailAdmin Laravel** UI shell (Tailwind CSS v4 + Alpine.js + Vite). It covers catalog, inventory, customers, orders, offers, point-of-sale checkout, and reports with Spatie roles & permissions.

## Stack

| Layer | Choice |
|---|---|
| PHP | **8.4+** (compatible with 8.5) |
| Framework | Laravel 12 |
| Database | **MySQL** 8 (recommended for single-device sessions) |
| Modules | `nwidart/laravel-modules` |
| AuthZ | `spatie/laravel-permission` |
| UI | TailAdmin layouts, Blade components, Alpine.js |
| Frontend tooling | Vite, Tailwind CSS v4, jQuery + jquery-validation, Axios |
| PDF / Excel | DomPDF, Maatwebsite Excel |
| Tests / style | Pest 4, Laravel Pint |

## Modules

| Module | Purpose |
|---|---|
| **User** | Staff accounts and Spatie role assignment |
| **Profile** | Own profile, password, login records |
| **Settings** | Currency + theme (maps to TailAdmin CSS vars) |
| **Category** | Product categories (separate from Brand/Product) |
| **Brand** | Brands + optional logo |
| **Product** | Catalog, UPC check workflow, SKU |
| **Inventory** | Stock in / out / adjust + movements + valuation |
| **Customer** | Customers including protected Walk-in |
| **Order** | Orders, payments, cancel/reversal, invoices |
| **Offer** | Coupons / discounts with usage limits |
| **Pos** | Scanner POS: scan, search, offer, Cash/Card/UPI |
| **Report** | Dashboard widgets + filtered reports + CSV/XLSX/PDF |
| **Core** | Shared helpers (optional utilities) |

Category, Brand, and Product stay **separate modules** by design.

## Seeded roles

`Super Admin`, `Admin`, `Inventory Manager`, `Supervisor`, `Cashier` — permissions include `users.*`, `products.*`, `categories.*`, `brands.*`, `inventory.view` / `inventory.adjust`, `orders.*`, `customers.manage`, `offers.manage`, `reports.view`, `pos.access`, `settings.manage`.

## Quick start

Requirements: PHP 8.4+, Composer, Node.js 22.x (see `package.json` engines), MySQL, npm.

```bash
# 1. Dependencies
composer install
npm install

# 2. Environment
copy .env.example .env          # Windows
# cp .env.example .env          # Unix

php artisan key:generate
```

Configure MySQL in `.env` (recommended for SmartBiz):

```env
APP_NAME=SmartBiz
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smartbiz
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
QUEUE_CONNECTION=sync
```

```bash
# 3. Database + seed
php artisan migrate --seed

# 4. Public storage (brand logos, uploads)
php artisan storage:link

# 5. Frontend
npm run build
# or during development: npm run dev

# 6. Serve
php artisan serve
# or: composer run dev   (serve + queue + logs + Vite)
```

Open [http://127.0.0.1:8000](http://127.0.0.1:8000) and sign in:

| Field | Value |
|---|---|
| Email | `admin@smartbiz.test` |
| Password | `password` |

The seeder creates **Super Admin** with that account, default Settings (INR / ₹ / theme `#0F766E`), and a Walk-in customer.

## Documentation

| Doc | Contents |
|---|---|
| [INSTALLATION.md](INSTALLATION.md) | Laragon / Windows setup, sessions, queue, storage |
| [USER_GUIDE.md](USER_GUIDE.md) | End-user walkthrough (Definition of Done path) |
| [API_DOCUMENTATION.md](API_DOCUMENTATION.md) | JSON AJAX contract and key endpoints |
| [TESTING_DOCUMENTATION.md](TESTING_DOCUMENTATION.md) | Running Pest and test map |
| [DEVELOPMENT_WAVES.md](DEVELOPMENT_WAVES.md) | Wave plan and implementation status |
| [ARCHITECTURE.md](ARCHITECTURE.md) | Module layering and cross-cutting rules |
| [DATABASE_DESIGN.md](DATABASE_DESIGN.md) | Schema overview |
| [PROJECT_PLAN.md](PROJECT_PLAN.md) | Product plan |
| [AGENT_TASKS.md](AGENT_TASKS.md) | Agent task checklist |

## UI & auth notes

- Admin chrome is **TailAdmin** throughout (sidebar, header, auth sign-in page).
- Forms use AJAX + dual validation (jQuery Validation client-side, Form Requests server-side).
- JSON responses follow a shared `ApiResponse` contract (`success`, `message`, `data`, `errors`).
- Single-device login prefers `SESSION_DRIVER=database` so other sessions for the same user can be deleted on login.

## License

Application code follows the project license; TailAdmin UI assets remain subject to [TailAdmin licensing](https://tailadmin.com/license).
# smartbiz-retail-management
