# SmartBiz – API / AJAX Documentation

SmartBiz admin screens use **session-authenticated JSON** endpoints (same-origin AJAX with CSRF), not a separate public REST API. Module `routes/api.php` stubs with `auth:sanctum` exist for some modules but the product UI talks to **web routes** under `auth` (+ Spatie permission middleware).

Send `Accept: application/json` (or `X-Requested-With: XMLHttpRequest`) and the Laravel CSRF token (`X-CSRF-TOKEN` / `_token`) on mutating requests.

## Shared response contract

All `App\Support\ApiResponse` payloads use:

### Success

```json
{
  "success": true,
  "message": "Human-readable message.",
  "data": {},
  "errors": {}
}
```

`errors` is an empty object on success. HTTP status is typically `200` or `201`.

### Validation / business error

```json
{
  "success": false,
  "message": "Error.",
  "data": {},
  "errors": {
    "field_name": ["First message"]
  }
}
```

Laravel may also return a standard `422` validation JSON (`message` + `errors`) when a Form Request fails before the controller wraps `ApiResponse`. Treat both shapes as failure; prefer reading `errors` for field messages.

Unauthorized: `401` / `403`. Guests hitting protected pages get redirected to login on HTML requests.

---

## Authentication

### Login

| | |
|---|---|
| **Method / URL** | `POST /login` |
| **Route name** | `login.store` |
| **Auth** | Guest |

**Request (form / JSON):**

```json
{
  "email": "admin@smartbiz.test",
  "password": "password",
  "remember": false
}
```

**Success (AJAX / expects JSON):** `200`

```json
{
  "success": true,
  "message": "Signed in successfully.",
  "data": {
    "redirect": "http://127.0.0.1:8000/dashboard"
  },
  "errors": {}
}
```

Non-AJAX form posts redirect to the dashboard. Failed credentials → `422` with `email` error. Successful login bumps `session_version` and, when `SESSION_DRIVER=database`, deletes other `sessions` rows for that user.

### Logout

`POST /logout` (auth) — redirects to login (HTML).

---

## Product – UPC check

| | |
|---|---|
| **Method / URL** | `GET /products/check-upc` |
| **Route name** | `product.check-upc` |
| **Permission** | `products.view` **or** `products.create` **or** `products.update` |

**Query:**

| Param | Type | Notes |
|---|---|---|
| `upc` | string | Raw UPC; normalized server-side |
| `ignore_id` | int? | Product id to ignore (edit form) |

**Success `data` shapes:**

Available:

```json
{
  "status": "available",
  "upc": "8901234567890",
  "product": null
}
```

Exists:

```json
{
  "status": "exists",
  "upc": "8901234567890",
  "product": {
    "id": 1,
    "name": "Example",
    "upc": "8901234567890",
    "sku": "EX-001",
    "category": "Snacks",
    "brand": "Acme",
    "is_active": true,
    "deleted": false,
    "edit_url": "http://127.0.0.1:8000/products/1/edit"
  }
}
```

Empty / still typing may return `"status": "checking"` with empty `upc`.

Related: `GET /products/suggest-sku?name=...` → `data.sku` (create/update permission).

---

## POS

All require auth + `pos.access`.

### Scan by UPC

| | |
|---|---|
| **Method / URL** | `GET /pos/scan` |
| **Route name** | `pos.scan` |

**Query:** `upc` (string)

**Success `data`:**

```json
{
  "product": {
    "id": 1,
    "name": "Example",
    "upc": "8901234567890",
    "sku": "EX-001",
    "selling_price": 99.0,
    "tax_percent": 0.0,
    "stock_quantity": 10.0,
    "category_id": 1,
    "brand_id": 1,
    "category": "Snacks",
    "brand": "Acme",
    "is_active": true,
    "unit": "pcs"
  }
}
```

Failures (inactive, missing, out of stock) → validation errors on `upc`.

### Product search

| | |
|---|---|
| **Method / URL** | `GET /pos/products/search` |
| **Route name** | `pos.products.search` |

**Query:** `search` or `q`, optional `category_id`, `brand_id`

**Success `data`:**

```json
{
  "products": [
    {
      "id": 1,
      "name": "Example",
      "upc": "8901234567890",
      "sku": "EX-001",
      "selling_price": 99.0,
      "tax_percent": 0.0,
      "stock_quantity": 10.0,
      "category_id": 1,
      "brand_id": 1,
      "category": "Snacks",
      "brand": "Acme",
      "is_active": true,
      "unit": "pcs"
    }
  ]
}
```

Only active products; limited result set (server default ~40).

### Validate offer

| | |
|---|---|
| **Method / URL** | `POST /pos/offers/validate` |
| **Route name** | `pos.offers.validate` |

**Request body:**

```json
{
  "customer_id": 1,
  "subtotal": 500.0,
  "coupon_code": "SAVE10",
  "offer_id": null,
  "items": [
    {
      "product_id": 1,
      "category_id": 2,
      "line_subtotal": 500.0
    }
  ]
}
```

`coupon` is accepted as an alias of `coupon_code`.

**Success `data`:**

```json
{
  "offer_id": 3,
  "discount_amount": 50.0,
  "offer": {
    "id": 3,
    "name": "10% off",
    "coupon_code": "SAVE10",
    "type": "percentage",
    "value": 10.0
  }
}
```

Invalid / expired / min-purchase failures → field validation errors.

### Checkout

| | |
|---|---|
| **Method / URL** | `POST /pos/checkout` |
| **Route name** | `pos.checkout` |
| **Status** | `201` on success |

**Request body:**

```json
{
  "checkout_token": "550e8400-e29b-41d4-a716-446655440000",
  "customer_id": 1,
  "notes": null,
  "coupon_code": "SAVE10",
  "offer_id": 3,
  "items": [
    {
      "product_id": 1,
      "quantity": 2,
      "discount_amount": 0
    }
  ],
  "payment": {
    "method": "cash",
    "amount_received": 500
  }
}
```

| Field | Rules |
|---|---|
| `checkout_token` | Required UUID; idempotency key (duplicate → rejected) |
| `customer_id` | Required; exists |
| `items` | Min 1; `product_id` + `quantity` (> 0) |
| `payment.method` | `cash` \| `card` \| `upi` |
| `payment.amount_received` | Required for **cash** |
| `payment.reference` | Required for **card** and **upi** |

Client totals (`subtotal`, `tax_amount`, `discount_amount`, `total`, line `unit_price` / `line_total`) may be sent for UI display but are **ignored** server-side; the server recalculates.

**Success `data`:**

```json
{
  "order": {
    "id": 10,
    "order_number": "ORD-...",
    "invoice_number": "INV-...",
    "customer_id": 1,
    "customer": "Walk-in Customer",
    "status": "completed",
    "payment_status": "paid",
    "subtotal": "198.00",
    "discount_amount": "0.00",
    "tax_amount": "0.00",
    "total": "198.00",
    "items": [
      {
        "product_id": 1,
        "product_name": "Example",
        "quantity": "2.000",
        "unit_price": "99.00",
        "line_total": "198.00"
      }
    ],
    "payments": [
      {
        "id": 5,
        "method": "cash",
        "amount": "198.00",
        "amount_received": "500.00",
        "change_amount": "302.00",
        "reference": null
      }
    ]
  },
  "print_url": "http://127.0.0.1:8000/orders/10/invoice",
  "redirect": "http://127.0.0.1:8000/orders/10/invoice"
}
```

Checkout creates the order, payment, stock `sale` movement(s), and offer usage inside a DB transaction with row locks. Oversell and reused tokens are rejected.

---

## Category – quick store

Used from the product create UI to add a category without leaving the form.

| | |
|---|---|
| **Method / URL** | `POST /categories/quick-store` |
| **Route name** | `category.quick-store` |
| **Permission** | `categories.create` |
| **Status** | `201` |

**Request:**

```json
{
  "name": "Beverages",
  "description": null,
  "parent_id": null,
  "is_active": true
}
```

**Success `data`:**

```json
{
  "category": {
    "id": 4,
    "name": "Beverages",
    "slug": "beverages",
    "description": null,
    "parent_id": null,
    "is_active": true
  }
}
```

Search helper: `GET /categories/search?q=...` → `data.results[]` with `id`, `text`, `name`.

---

## Brand – quick store

| | |
|---|---|
| **Method / URL** | `POST /brands/quick-store` |
| **Route name** | `brand.quick-store` |
| **Permission** | `brands.create` |
| **Status** | `201` |

**Request:** multipart allowed when uploading `logo` (`jpg|jpeg|png|webp`, max 2 MB).

```json
{
  "name": "Acme",
  "description": null,
  "is_active": true
}
```

**Success `data`:**

```json
{
  "brand": {
    "id": 2,
    "name": "Acme",
    "slug": "acme",
    "description": null,
    "logo": null,
    "logo_url": null,
    "is_active": true
  }
}
```

Search helper: `GET /brands/search?q=...` → `data.results[]`.

---

## Other JSON write patterns (summary)

Most CRUD screens follow the same `ApiResponse` envelope:

| Area | Examples |
|---|---|
| Products | `POST /products`, `PUT|POST /products/{id}`, `DELETE /products/{id}` |
| Categories / Brands | Full CRUD under `/categories`, `/brands` |
| Inventory | `POST /inventory/stock-in`, `/stock-out`, `/adjust` |
| Customers / Orders / Offers | Module web routes with permission gates |
| Settings | `PUT|POST /settings` (`settings.manage`) |
| Profile | `PUT|POST /profile`, `POST /profile/password` |

Report exports are primarily **file downloads** (CSV / XLSX / PDF) under `/reports/.../export/{format}`, not JSON.

---

## Permissions quick reference

| Capability | Permission |
|---|---|
| POS | `pos.access` |
| UPC check | `products.view` / `create` / `update` |
| Category quick-store | `categories.create` |
| Brand quick-store | `brands.create` |
| Reports UI | `reports.view` |
| Settings | `settings.manage` |
