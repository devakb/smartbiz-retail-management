# SmartBiz – Testing Documentation

SmartBiz uses **Pest 4** with the Laravel plugin. Feature tests live under `tests/Feature` and extend `Tests\TestCase` (see `tests/Pest.php`).

## Prerequisites

1. PHP 8.4+, Composer dependencies installed (`composer install`).
2. MySQL available locally (Laragon recommended).
3. Create a dedicated test database (matches `phpunit.xml`):

```sql
CREATE DATABASE smartbiz_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

`phpunit.xml` forces:

| Env | Value |
|---|---|
| `DB_CONNECTION` | `mysql` |
| `DB_DATABASE` | `smartbiz_testing` |
| `DB_USERNAME` | `root` |
| `DB_PASSWORD` | _(empty)_ |
| `SESSION_DRIVER` | `array` |
| `QUEUE_CONNECTION` | `sync` |
| `CACHE_STORE` | `array` |

Override credentials via environment or a local `phpunit.xml` copy if your MySQL root password is set.

## How to run

Full suite:

```powershell
php artisan test
# or
composer run test
# or
./vendor/bin/pest
```

Filter by file or name:

```powershell
php artisan test --filter=PosCheckoutTest
php artisan test tests/Feature/OfferValidationTest.php
./vendor/bin/pest --filter="checkout cash"
```

Optional coverage (requires Xdebug / PCOV):

```powershell
php artisan test --coverage
```

Code style (not part of Pest, but Wave 8 QA):

```powershell
./vendor/bin/pint
```

Most Feature tests call `uses(RefreshDatabase::class)` so each file migrates a clean schema. Seeders are invoked inside tests as needed (roles, walk-in, settings), not via a global `DatabaseSeeder` on every test.

## Major test files / areas

| File | Area covered |
|---|---|
| `tests/Feature/AuthenticationTest.php` | Guest redirect; login with credentials; `session_version` |
| `tests/Feature/SingleDeviceLoginTest.php` | Second login invalidates prior session |
| `tests/Feature/ProfileUpdateTest.php` | Profile page + name/email update |
| `tests/Feature/SettingsUpdateTest.php` | `settings.manage` gate; currency/theme update |
| `tests/Feature/UserIndexTest.php` | `users.view`; self-delete blocked |
| `tests/Feature/CategoryCrudTest.php` | Category permissions + create/update |
| `tests/Feature/BrandCrudTest.php` | Brand permissions + create |
| `tests/Feature/ProductUpcTest.php` | UPC uniqueness; `/products/check-upc` available vs exists |
| `tests/Feature/InventoryStockTest.php` | Stock in; stock out cannot go negative; adjust requires reason |
| `tests/Feature/CustomerOrderTest.php` | Customer create; Walk-in protected; order stock deduct; cancel reversal; server totals |
| `tests/Feature/OfferValidationTest.php` | Expired/inactive/min purchase/usage limit; % discount + max cap |
| `tests/Feature/PosCheckoutTest.php` | Cash/Card/UPI checkout; oversell blocked; duplicate token; inactive scan/sale |
| `tests/Feature/ReportDashboardTest.php` | Dashboard auth; `reports.view`; sales + low-stock reports |
| `tests/Feature/ExampleTest.php` | Smoke / baseline |
| `tests/Unit/ExampleTest.php` | Unit smoke |

## What “green” should mean for DoD

A healthy Wave 8 suite should cover the critical path:

1. Auth + single-device session exclusivity  
2. Settings + profile  
3. Catalog (category / brand / UPC)  
4. Inventory ledger constraints  
5. Customers / orders / offer rules  
6. POS payment methods + stock safety + idempotent checkout  
7. Dashboard / reports permissioning  

Re-run the full suite after schema or checkout changes before release.
