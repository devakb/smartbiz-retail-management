# SmartBiz – Development Waves

Strict sequence was: **Phase 1 → Wave 0 → Waves 1–8**. After each wave: Pest subset → Pint → fix blockers before the next wave.

**Status (Wave 8):** Phase 1 and Waves **0–8** are **implemented**. Docs below are the historical plan with brief completion notes.

---

## Phase 1 – Installation & setup — **implemented**

TailAdmin Laravel shell, MySQL-oriented `.env`, Spatie Permission, nwidart modules, DomPDF, Excel, Pest, Pint, jQuery/validation/Axios on Vite, module skeletons, AJAX/toast helpers, Spatie middleware + auth UI.

---

## Wave 0 – Planning docs — **implemented**

Root docs: `PROJECT_PLAN.md`, `ARCHITECTURE.md`, `DATABASE_DESIGN.md`, `DEVELOPMENT_WAVES.md`, `AGENT_TASKS.md`. Locked decisions: TailAdmin UI, separate Category/Brand/Product, Settings, Profile, login records, single-device login, AJAX contract, no warehouses.

---

## Wave 1 – Foundation — **implemented**

Domain migrations/models, roles/permissions + Super Admin + Walk-in + Settings defaults, User CRUD, Profile + login records, single-device login, Settings currency/theme, permission-gated TailAdmin nav, baseline Pest (auth, profile, settings, session exclusivity).

---

## Wave 2 – Category, Brand, Product & Inventory — **implemented**

Category/Brand CRUD (AJAX), Product UPC workflow + quick-store category/brand, Inventory stock in/out/adjust + movements/valuation, Pest for UPC and stock rules.

---

## Wave 3 – Customers & orders — **implemented**

Customer CRUD (Walk-in protected), orders create/view/status/cancel, stock deduct once / cancel reversal, invoice view/PDF, payments, Pest for totals and reversal.

---

## Wave 4 – Offers — **implemented**

Offer CRUD, validate/calculate service, usage limits, Pest for expired/inactive/min/usage/% + max.

---

## Wave 5 – POS — **implemented**

POS UI (scan/search/cart/customer/offer), Cash/Card/UPI checkout with idempotency + locks, invoice print URL, Pest for payments/oversell/duplicate token/inactive product.

---

## Wave 6 – Reports & analytics — **implemented**

Dashboard widgets; reports (sales, stock, inventory, low stock, product performance, customers, payments, offers, profitability) with filters and CSV/XLSX/(PDF where applicable); `reports.view` gating.

---

## Wave 7 – UI/UX, security, integration — **implemented**

TailAdmin patterns across modules, theme from Settings, CSRF + permission gates, single-device middleware on protected routes, AJAX/toast/validation consistency, Profile + Settings polish.

---

## Wave 8 – QA & deployment docs — **implemented**

Full Pest suite on critical paths; Pint; shipping docs:

- `README.md`
- `INSTALLATION.md`
- `API_DOCUMENTATION.md`
- `TESTING_DOCUMENTATION.md`
- `USER_GUIDE.md`
- this file refreshed for status

Production notes documented: `SESSION_DRIVER=database`, `storage:link`, cache, `QUEUE_CONNECTION=sync`.

---

## Definition of Done (end-to-end)

Admin login → TailAdmin dashboard → Settings → users/roles → profile + login records → categories → brands → UPC product → stock in → Cashier POS (single-device) → cart → Walk-in/customer → offer → Cash/Card/UPI → order + payment + stock movement → invoice → reports.

Automated tests on critical paths. Forms AJAX + dual validation. Category, Brand, Product stay separate. Admin chrome is TailAdmin throughout.
