# SmartBiz – Installation

Target environment: **Windows + Laragon** (or equivalent local PHP/MySQL stack). PHP **8.4+** (8.5 OK), Composer, Node **22.x**, MySQL 8.

## 1. Laragon / Windows notes

1. Install [Laragon](https://laragon.org/) with PHP 8.4+ and MySQL enabled.
2. Place the project under Laragon’s `www` folder (or any path served by `php artisan serve`).
3. In Laragon menu: **Menu → PHP → version** → select 8.4 or 8.5.
4. Start **Apache/Nginx** (optional if using `artisan serve`) and **MySQL**.
5. Open a terminal in the project root (`E:\…\543543554455435345435435` or your clone path).

Verify tools:

```powershell
php -v
composer -V
node -v
npm -v
```

Composer’s `composer.json` requires `"php": "^8.4"`. If Laragon still points at an older PHP, switch the active version before `composer install`.

## 2. Install dependencies

```powershell
composer install
npm install
```

## 3. Environment

```powershell
copy .env.example .env
php artisan key:generate
```

Recommended `.env` for SmartBiz on Laragon:

```env
APP_NAME=SmartBiz
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smartbiz
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
SESSION_LIFETIME=120

QUEUE_CONNECTION=sync
CACHE_DRIVER=file
FILESYSTEM_DISK=local
```

Create the database in HeidiSQL / phpMyAdmin / CLI:

```sql
CREATE DATABASE smartbiz CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Why `SESSION_DRIVER=database`?

Single-device login deletes other rows for the same `user_id` in the `sessions` table on successful login. File sessions cannot do that reliably. Prefer **database** sessions in local and production.

`.env.example` may still show `SESSION_DRIVER=file` and SQLite defaults from the TailAdmin skeleton — override them as above for SmartBiz.

## 4. Migrate, seed, storage link

```powershell
php artisan migrate --seed
php artisan storage:link
```

`storage:link` is required for brand logos and other public disk uploads (`storage/app/public` → `public/storage`).

Default login after seed:

- **Email:** `admin@smartbiz.test`
- **Password:** `password`
- **Role:** Super Admin

Also seeded: Spatie roles/permissions, Settings (INR / ₹ / theme `#0F766E`), Walk-in customer.

## 5. Frontend build

Development (Vite HMR):

```powershell
npm run dev
```

Production / one-shot assets:

```powershell
npm run build
```

Without a built `public/build` manifest, Blade `@vite` pages will fail to load CSS/JS. Run `npm run build` at least once if you are not running `npm run dev`.

## 6. Queue

SmartBiz is fine with synchronous queues for typical installs:

```env
QUEUE_CONNECTION=sync
```

No separate `queue:work` process is required when `sync` is set. Jobs (if any) run inline in the HTTP request.

If you later switch to `database` or `redis` queues, run:

```powershell
php artisan queue:work
```

`composer run dev` already starts `queue:listen` alongside serve/Vite for convenience.

## 7. Serve the app

Option A — Artisan only (run Vite or use a prior `npm run build`):

```powershell
php artisan serve
```

Option B — full local stack:

```powershell
composer run dev
```

Then open `http://127.0.0.1:8000/login`.

### Laragon virtual host (optional)

If you map a host such as `smartbiz.test`:

1. Set `APP_URL=http://smartbiz.test` in `.env`.
2. Ensure the document root points at the project’s `public/` directory.
3. Re-run `php artisan config:clear` after changing `APP_URL`.

## 8. Production checklist (brief)

- `APP_ENV=production`, `APP_DEBUG=false`, strong `APP_KEY`
- MySQL credentials and backups
- `SESSION_DRIVER=database`
- `QUEUE_CONNECTION=sync` (or a managed worker)
- `php artisan storage:link`
- `npm run build`
- `php artisan config:cache`, `route:cache`, `view:cache`
- `composer install --optimize-autoloader --no-dev`

## 9. Common Windows issues

| Symptom | Fix |
|---|---|
| `composer` platform PHP too old | Switch Laragon PHP to 8.4+ |
| Brand logos 404 | Run `php artisan storage:link` as Administrator if symlink creation fails |
| Sessions not exclusive | Set `SESSION_DRIVER=database` and migrate so `sessions` table exists |
| Vite / missing assets | `npm install` then `npm run build` or `npm run dev` |
| MySQL connection refused | Start MySQL in Laragon; confirm `DB_*` host/port |
| Tests fail on DB | Create `smartbiz_testing` (see [TESTING_DOCUMENTATION.md](TESTING_DOCUMENTATION.md)) |

## Related docs

- [README.md](README.md) — overview and quick start
- [USER_GUIDE.md](USER_GUIDE.md) — using the app after install
- [TESTING_DOCUMENTATION.md](TESTING_DOCUMENTATION.md) — Pest setup
