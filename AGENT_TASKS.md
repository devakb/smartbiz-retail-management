# SmartBiz – Agent Tasks & Ownership

Phase 2 multi-agent model. **Orchestrator** owns sequencing, shared layout/AJAX/theme injection, cross-module coordination, seeders, and merge gates. Parallel agents only when module ownership does not overlap.

## Ownership matrix

| Agent | Owns | Must not touch | Primary paths |
|---|---|---|---|
| **Architect** | Docs, conventions, shared response helpers | Feature CRUD | `PROJECT_PLAN.md`, `ARCHITECTURE.md`, `DATABASE_DESIGN.md`, `DEVELOPMENT_WAVES.md`, `AGENT_TASKS.md`; optional `Modules/Core` helpers |
| **Database** | Migrations (incl. `settings`, `login_records`, session fields), factories | Service business logic | `Modules/*/Database/Migrations`, factories; Spatie publish migrations as needed |
| **Backend Core** | User module, Spatie seed, single-device middleware, Profile + Settings services | POS UI, reports | `Modules/User`, `Modules/Profile`, `Modules/Settings`; auth middleware; role/permission seeders |
| **Frontend Shell** | TailAdmin layouts/sidebar/header, toast, ajax-form, Settings→theme tokens, permission-gated nav | Domain services | `resources/views/layouts`, `resources/views/components`, `resources/js/app.js`, `resources/css/app.css` |
| **Catalog** | **Category**, **Brand**, **Product** (separate modules) | Order / POS / Offer | `Modules/Category`, `Modules/Brand`, `Modules/Product` |
| **Inventory** | Inventory module (stock/movements) | POS checkout | `Modules/Inventory` |
| **Orders & Customers** | Customer + Order modules | POS live cart views | `Modules/Customer`, `Modules/Order` |
| **Offers** | Offer module | POS Blade (API/service only) | `Modules/Offer` |
| **POS** | Pos module + checkout services (reads currency from Settings) | Report queries | `Modules/Pos` |
| **Reports** | Report module + exports | Checkout path | `Modules/Report` |
| **QA** | Pest (profile, settings, single-device), Pint, DoD | Shipping with failing tests | `tests/`, Pint; docs in Wave 8 |

## File boundary rules

1. **One module owner** — do not edit another agent’s `Modules/{Name}` tree without orchestrator merge.
2. **Shared shell** — Frontend Shell owns TailAdmin chrome; domain agents add Blade pages that *extend* those layouts and register nav items via agreed hooks (not by rewriting sidebar wholesale).
3. **Migrations** — Database agent owns schema files; other agents request columns rather than inventing competing migrations for the same table.
4. **Services** — Business rules live in the owning module’s `Services/`. Cross-module calls go through public service APIs, not reaching into another module’s private controllers.
5. **Offers ↔ POS** — Offers agent exposes `OfferService`; POS consumes it. Offers agent does not edit POS Blade.
6. **Inventory ↔ Order/POS** — Inventory owns `StockMovementService` and stock mutations; Order/POS call that service inside their transactions.
7. **Settings currency** — Settings owns storage/formatter; Order, POS, Report read formatter — do not hardcode currency symbols.
8. **Core (optional)** — Architect/Backend Core may place JSON response + audit helpers in `Modules/Core` or thin `app/` helpers; domain modules consume, not fork.

## Wave ↔ agent map

| Wave | Lead agents | Notes |
|---|---|---|
| 0 | Architect | Docs only |
| 1 | Database → Backend Core + Frontend Shell | Migrations first; then User/Profile/Settings/session; TailAdmin nav |
| 2 | Catalog + Inventory | Parallel: Category ‖ Brand; Product after Category/Brand APIs; Inventory parallel on stock APIs |
| 3 | Orders & Customers | Customer early; can parallel Catalog remnants / Offer prep |
| 4 | Offers | Can parallel Order work where files do not collide |
| 5 | POS | After Product + Stock + Customer + Offer + Order/Payment + Settings currency |
| 6 | Reports | After Orders + Payments + Stock movements |
| 7 | Frontend Shell + Backend Core + all module owners (polish) | Theme, audit, security pass |
| 8 | QA (+ Architect for docs) | Full Pest, Pint, user/install docs |

## Safe parallel windows (after Wave 1)

- Wave 2: Category ‖ Brand ‖ early Inventory read APIs; Product after Category/Brand APIs exist.
- Wave 2 Catalog ‖ Customer CRUD (Wave 3A).
- Wave 4 Offers ‖ Wave 3B Orders (non-overlapping files).
- Wave 5 POS only after Product + Stock + Customer + Offer + Order/Payment + Settings currency.
- Wave 6 Reports only after Orders + Payments + Stock movements.

## Merge gates (orchestrator)

After each wave:

1. Run affected Pest subset.
2. Run Pint.
3. Fix blockers before starting the next wave.
4. Confirm no module merge of Category/Brand/Product and no warehouse tables introduced.

## Shared contracts (all agents)

- AJAX JSON: `{ success, message, data, errors }`.
- Dual validation: jQuery Validation + Form Requests.
- Thin controllers; Services + Policies.
- Spatie permissions on routes and UI.
- Money `decimal`; UPC/SKU unique; no auto UPC.
- Single-device session middleware respected on all authenticated routes.
