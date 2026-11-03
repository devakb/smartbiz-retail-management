# SmartBiz – User Guide

End-user walkthrough aligned with the product **Definition of Done**: configure the shop, manage users and catalog, receive stock, sell on POS (Cash / Card / UPI), then review reports.

Default admin after install:

- URL: `/login`
- Email: `admin@smartbiz.test`
- Password: `password`

The sidebar is **permission-gated**. Super Admin sees everything; Cashiers see a smaller set (POS, products view, orders create, customers, etc.).

---

## 1. Sign in

1. Open the app and go to **Sign In**.
2. Enter email and password.
3. You land on the **Dashboard** (`/dashboard`) with sales / inventory widgets.

**Single-device login:** signing in elsewhere ends the previous session. You may see a message that you were signed in on another device.

**Profile:** use **Profile** in the menu to update your name/email, change password, and view **login records**.

---

## 2. Settings (currency & theme)

Requires `settings.manage`.

1. Open **Settings**.
2. Set **currency code**, **symbol**, **position** (before/after amount), and **decimals**.
3. Pick a **theme color** (TailAdmin primary accent).
4. Save — amounts on invoices, POS, and reports use these settings.

Seeded defaults: INR, ₹, before amount, 2 decimals, theme `#0F766E`.

---

## 3. Users & roles

Requires `users.view` / `create` / `edit` / `delete` as appropriate.

1. Open **Users**.
2. Create staff accounts and assign a Spatie role:
   - **Super Admin** / **Admin** — full or near-full access  
   - **Inventory Manager** — catalog + stock + reports  
   - **Supervisor** — sales, offers, POS, reports (limited catalog edit)  
   - **Cashier** — POS, view catalog/stock, create orders, manage customers  
3. You cannot delete your own user account.

Roles and permissions are seeded; Super Admin has all permissions.

---

## 4. Catalog – categories, brands, products

Keep these separate screens (by design).

### Categories (`/categories`)

1. Create categories (optional parent).
2. Activate/deactivate as needed.

### Brands (`/brands`)

1. Create brands.
2. Optionally upload a logo (needs `storage:link` on the server).

### Products (`/products`)

1. Click **Create**.
2. Scan or type a **UPC** — the app checks uniqueness via AJAX (`available` vs `exists`). SmartBiz **never auto-generates UPCs**.
3. Fill name, SKU (suggestion available), prices, tax %, unit, stock floor/reorder fields as shown.
4. Pick **Category** and **Brand**. If missing, use **quick create** from the product form (permission permitting).
5. Save. Edit later from the product list.

---

## 5. Stock / inventory

Requires `inventory.view`; adjustments need `inventory.adjust`.

1. Open **Inventory**.
2. **Stock in** — receive goods (quantity + reason/notes as prompted).
3. **Stock out** — issue stock (cannot drive quantity below zero).
4. **Adjust** — corrections; a **reason is required**.
5. Review **Movements** and **Valuation** from the inventory screens.

Every change writes a `stock_movements` ledger row. Sales and cancellations also move stock automatically (`sale` / `sale_reversal`).

---

## 6. Customers & offers (optional before POS)

### Customers

1. Open **Customers** to add regular buyers.
2. A **Walk-in** customer is seeded and protected (do not delete).
3. POS defaults to Walk-in when no other customer is selected.

### Offers

1. Open **Offers** (`offers.manage`).
2. Create percentage or fixed discounts with coupon code, min purchase, max discount, usage limits, and product/category/order scope.
3. Activate the offer before using it on POS.

---

## 7. POS checkout (Cash / Card / UPI)

Requires `pos.access`.

1. Open **POS** (`/pos`).
2. **Scan** a UPC or **search** by name/SKU/UPC; optionally filter by category/brand.
3. Build the cart; set quantities. Inactive or out-of-stock items are blocked.
4. Confirm **customer** (Walk-in or selected customer).
5. Optionally enter a **coupon** / offer — the server validates and returns the discount.
6. Choose payment:
   - **Cash** — enter amount received; change is calculated.
   - **Card** — enter a payment **reference**.
   - **UPI** — enter a payment **reference**.
7. Complete checkout. The system creates the order, payment, stock deduction, and invoice link in one step.
8. Open / print the **invoice** from the returned print URL or **Orders**.

Notes:

- Totals are recalculated on the server; tampering with client totals does not change the bill.
- Re-submitting the same checkout token is rejected (duplicate sale protection).
- Overselling is blocked when stock is insufficient.

### Orders (back office)

From **Orders** you can list sales, view details, update status, cancel (stock reversal), record additional payments (where permitted), and download invoice PDF.

---

## 8. Reports & dashboard

Dashboard (`/dashboard`) is available to authenticated users.

Detailed reports need `reports.view` under **Reports**:

| Report | Path |
|---|---|
| Index | `/reports` |
| Sales | `/reports/sales` (+ CSV / XLSX / PDF export) |
| Stock movements | `/reports/stock-movements` |
| Inventory | `/reports/inventory` |
| Low stock | `/reports/low-stock` |
| Product performance | `/reports/product-performance` |
| Customer purchases | `/reports/customer-purchases` |
| Payment methods | `/reports/payment-methods` |
| Offer usage | `/reports/offer-usage` |
| Profitability | `/reports/profitability` |

Use date and other filters on each screen, then export as needed.

---

## Definition of Done checklist

Use this as a manual smoke path after install or release:

1. Admin login → TailAdmin dashboard  
2. Settings (currency + theme)  
3. Users / roles  
4. Profile + login records  
5. Categories → brands → product with UPC  
6. Stock in  
7. Cashier (or admin) POS: cart → Walk-in/customer → optional offer → **Cash / Card / UPI**  
8. Confirm order + payment + stock movement + invoice  
9. Open reports  

Forms should save via AJAX with toast feedback; Category, Brand, and Product remain separate modules/screens; admin chrome stays TailAdmin throughout.
