# SmartBiz – Project Plan

Single-business Inventory, Orders, Offers, Reports, and POS application. Admin UI is **TailAdmin Laravel** throughout. Stock is product-level only (**no warehouses**).

## Locked decisions

| Decision | Choice |
|---|---|
| Tenancy | Single-business (roles/permissions only) |
| Inventory | `products.stock_quantity` + `stock_movements` — no warehouses, no transfers |
| Catalog | Separate modules: **Category**, **Brand**, **Product** |
| Settings | Currency (code, symbol, decimals, position) + theme color |
| Profile | Name, email, password; view own login records |
| Sessions | **Single-device login** — one active session per user |
| Roles | Super Admin, Admin, Inventory Manager, Supervisor, Cashier |
| Admin UI | TailAdmin Laravel (Blade layouts, sidebar, header, components) |
| AuthZ | Spatie Laravel Permission |
| Modules | `nwidart/laravel-modules` |
| Forms | AJAX-only + jQuery Validation + toast |
| Money | `decimal(12,2)`; currency is display/config only |
| UPC/SKU | Unique; **never auto-generate UPC** |
| Tests | Pest; formatting via Pint |
| PDF / Excel | DomPDF / Maatwebsite Excel |
| PHP / Laravel | PHP ^8.2; latest Laravel compatible at scaffold |

## Goal

Deliver a production-ready SmartBiz admin + POS stack: catalog, stock ledger, customers/orders, offers, POS checkout, and reports — all under TailAdmin, modular Laravel, dual validation, and Spatie permissions.

## Modules

| Module | Responsibility |
|---|---|
| `User` | Admin user CRUD, role/permission assignment |
| `Profile` | Own name/email/password; own login records |
| `Settings` | Currency + theme color (`settings.manage`) |
| `Category` | Category CRUD (parent, status, search, pagination) |
| `Brand` | Brand CRUD (logo, status) |
| `Product` | Product CRUD + UPC workflow; Category/Brand selects |
| `Inventory` | Stock in/out/adjust, movements, low stock, valuation |
| `Customer` | Customers, Walk-in, purchase history |
| `Order` | Orders, items, cancel/reversal, invoices |
| `Offer` | Offers, scopes, usages, discount calc |
| `Pos` | POS UI, checkout orchestration |
| `Report` | Dashboard widgets, reports, exports |
| `Core` (optional) | Shared traits, audit helpers, JSON response helper |

## Stack

- **Backend:** Modular Laravel, Eloquent, Form Requests, Policies, Services, Spatie Permission
- **Frontend:** TailAdmin Laravel (Tailwind v4, Alpine, Vite) + jQuery + jQuery Validation + Axios/Fetch + toasts
- **DB:** MySQL 8
- **Quality:** Pest, Pint, CSRF, Spatie on routes and UI

## Engineering standards

1. Thin controllers → Services (business rules) → Form Requests → Policies/Spatie → Models (persistence).
2. Prefer simple, reusable code; no redundant layers or packages without a clear reason.
3. Eager load; indexed queries; never trust browser totals or client-only validation.
4. Security: CSRF, hashed passwords, upload validation, single-device session enforcement.
5. Pint formatting; consistent Laravel naming.

## Dual validation & AJAX contract

- **Client:** jQuery Validation on every form; block submit until valid; disable submit while AJAX in flight.
- **Server:** Form Requests on every write.
- **Submit:** AJAX only (Axios or Fetch). No full-page reload on success paths.
- **JSON shape:**

```json
{
  "success": true,
  "message": "Product created successfully.",
  "data": {},
  "errors": {}
}
```

- `success: false` + `errors` on validation (422). Toast for success/error/warning/info. Handle 403/419/500 without leaking internals.

## Critical business rules

- UPC/SKU unique; normalize UPC server-side; inactive products not sellable.
- Stock ≥ 0; every change writes a movement inside a transaction with `lockForUpdate`.
- Order/POS totals computed server-side; line items store historical prices.
- One stock deduction per sale; cancel → `sale_reversal` when applicable.
- Offers validated server-side; default one offer per order.
- Card/UPI refs recorded without claiming real payment gateways.
- Currency/theme only via Settings (`settings.manage`).
- One active login per user; new login revokes previous.
- Users edit own profile and view own login records (current password required for password change).

## Delivery sequence

1. **Phase 1** — Bootstrap TailAdmin + Laravel modules + Spatie + AJAX/toast/validation assets; verify boot.
2. **Wave 0** — This documentation set.
3. **Waves 1–8** — Foundation → Catalog/Inventory → Orders → Offers → POS → Reports → Polish → QA/docs.

Do **not** start feature waves until Phase 1 exit criteria pass. Integration gate after each wave: Pest subset → Pint → fix blockers.

## Definition of Done

Admin login (TailAdmin) → dashboard → Settings (currency/theme) → users/roles → profile + login records → categories → brands → UPC product → stock in → Cashier POS (single-device) → cart → Walk-in/customer → offer → Cash/Card/UPI → order + payment + stock movement → invoice → reports.

Critical paths covered by Pest. All forms AJAX + dual validation. Category, Brand, Product remain separate modules. Admin chrome is TailAdmin throughout.

## Related docs

- [ARCHITECTURE.md](ARCHITECTURE.md)
- [DATABASE_DESIGN.md](DATABASE_DESIGN.md)
- [DEVELOPMENT_WAVES.md](DEVELOPMENT_WAVES.md)
- [AGENT_TASKS.md](AGENT_TASKS.md)
