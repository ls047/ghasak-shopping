# Sunny Mart — Project documentation

A **Word version** of this document is **`DOCUMENTATION.docx`** in the same folder. To regenerate it after edits, run: `python scripts/build_documentation_docx.py` (requires `python-docx`: `pip install python-docx`).

| | |
| :--- | :--- |
| **Student name** | |
| **Date** | |

---

## Abstract

This project is a small e-commerce web application built for learning classic server-side web development. The **storefront** lets visitors browse products, manage a session-based shopping cart, and place orders that are stored in a **SQLite** database. A separate **admin dashboard** (protected by login) is used to create, edit, and delete products and to review orders. The user interface is split into plain **HTML template files** (with `{{placeholder}}` markers), **CSS** for styling, and a little **JavaScript** where helpful; **PHP** handles HTTP requests, database access, security-sensitive output escaping, and assembling the final HTML. The design keeps structure (templates), behavior (PHP), and presentation (CSS) relatively separate so it is easier to read and extend.

---

## Table of contents

1. [How the project works](#1-how-the-project-works)
2. [How to run the project](#2-how-to-run-the-project)
3. [URLs and paths](#3-urls-and-paths)
4. [Folder and file structure](#4-folder-and-file-structure)
5. [Database](#5-database)
6. [Security notes (teaching points)](#6-security-notes-teaching-points)

---

## 1. How the project works

### 1.1 Overall flow

1. The browser requests a **PHP page** (for example `index.php`).
2. PHP loads **`includes/bootstrap.php`**, which starts the **session**, connects configuration, defines helpers (`h`, `redirect`, `money_format_shop`), and loads **`includes/template.php`**.
3. Store pages also load **`includes/cart.php`**, which reads/writes the cart in **`$_SESSION['cart']`**.
4. PHP talks to **SQLite** through **`config/database.php`** (`Database::pdo()`). The database file is created automatically on first use.
5. PHP builds strings of HTML by loading **`.html` files** from **`templates/`** and replacing **`{{name}}`**-style placeholders via the **`template()`** function. Dynamic values that come from users or the database should be passed through **`h()`** before insertion to reduce XSS risk.
6. **`shop_header()` / `shop_footer()`** (or **`admin_header()` / `admin_footer()`**) print the shared layout; the middle of the page is filled by page-specific templates.
7. The browser receives normal HTML and loads **`css/style.css`** and **`js/*.js`** using paths relative to the site root (see [§3](#3-urls-and-paths)).

### 1.2 Storefront (public site)

| File | Role |
|------|------|
| `index.php` | Lists active products; **POST** adds lines to the cart (respects stock). |
| `cart.php` | Shows cart; **POST** can update quantities or clear the cart. |
| `checkout.php` | If the cart has items, shows a form; **POST** creates an **order**, **order_items**, decreases **stock**, clears the cart. |

### 1.3 Admin area

| File | Role |
|------|------|
| `admin/login.php` | Login form; sets session on success. |
| `admin/logout.php` | Clears admin session. |
| `admin/index.php` | Product list with edit/delete. |
| `admin/product-form.php` | Create or edit a product. |
| `admin/product-delete.php` | **POST** handler to delete a product. |
| `admin/orders.php` | Lists orders; optional `?id=` shows line items. |

**`includes/admin_auth.php`** defines **`admin_logged_in()`**, **`admin_require_login()`**, etc. Protected admin pages call **`admin_require_login()`** first.

### 1.4 Template placeholders

Templates live under **`templates/shop/`** and **`templates/admin/`**. Any text like **`{{page_title}}`** is replaced by PHP when calling:

```php
template('shop/header.html', ['page_title' => h('Products'), ...]);
```

Some placeholders receive **small HTML fragments** built in PHP (for example a list of table rows); those fragments must only contain data that PHP has already escaped where needed.

---

## 2. How to run the project

### 2.1 Requirements

- **PHP 8+** with extensions: **PDO**, **pdo_sqlite**, **session**, **password** (default in most PHP builds).

### 2.2 Option A — PHP built-in server (quick test)

From the project root (the folder that contains `index.php`):

```bash
php -S localhost:8080
```

Then open:

- Store: `http://localhost:8080/`
- Admin: `http://localhost:8080/admin/login.php`

### 2.3 Option B — XAMPP (Apache)

1. Copy or move the whole project folder into **`C:\xampp\htdocs\`** (for example **`htdocs\gassaq\`**).
2. Start **Apache** in the XAMPP Control Panel (MySQL is not required).
3. Open:

- Store: `http://localhost/gassaq/`
- Admin: `http://localhost/gassaq/admin/login.php`

The **`data/`** directory must be **writable** by the web server so SQLite can create/update **`shop.db`**.

### 2.4 Default admin login

| Field | Value |
|--------|--------|
| Username | `admin` |
| Password | `admin123` |

(If you set **`SHOP_ADMIN_PASSWORD`** in the environment **before** the database is first created, that value is used instead when seeding the admin user.)

---

## 3. URLs and paths

### 3.1 Important paths on disk (project root = folder containing `index.php`)

| Path | Meaning |
|------|---------|
| `config/database.php` | Database bootstrap and schema creation. |
| `includes/` | Shared PHP: bootstrap, templates, cart, admin auth. |
| `templates/` | HTML-only layouts and fragments. |
| `css/style.css` | Global styles. |
| `js/` | Client scripts. |
| `data/shop.db` | SQLite file (created at runtime; listed in `data/.gitignore`). |
| `admin/` | Admin PHP entry points. |

### 3.2 URL paths in the browser

These are relative to your **document root** (or the built-in server root):

| URL (example) | Script |
|-----------------|--------|
| `/index.php` or `/` (if directory index is set) | Store home |
| `/cart.php` | Cart |
| `/checkout.php` | Checkout |
| `/admin/login.php` | Admin login |

### 3.3 Paths inside HTML templates

- **Store** templates use **`{{base_path}}`** = **`.`** (same folder as `index.php` for assets), so CSS is linked as **`./css/style.css`** from the browser’s perspective when the script is at the root.
- **Admin** templates use **`{{root_path}}`** = **`..`** so that from **`/admin/*.php`** the stylesheet resolves to **`../css/style.css`**.

PHP constant **`ROOT_PATH`** (in `bootstrap.php`) is the **absolute filesystem path** to the project root, used only on the server to load template files — not to be confused with the URL path.

### 3.4 `BASE_URL` and redirects

**`BASE_URL`** is derived from **`dirname($_SERVER['SCRIPT_NAME'])`** so **`redirect('cart.php')`** sends the user to the correct folder whether the app lives at the server root or in a subdirectory (for example **`/gassaq/`**).

---

## 4. Folder and file structure

```
gassaq/
├── DOCUMENTATION.md          ← this file
├── index.php                 ← Store: product list
├── cart.php                  ← Store: cart
├── checkout.php              ← Store: checkout & order creation
├── config/
│   └── database.php          ← SQLite path, tables, sample data, admin seed
├── includes/
│   ├── bootstrap.php         ← Session, ROOT_PATH, BASE_URL, helpers
│   ├── template.php          ← template(), shop_header/footer, admin_header/footer
│   ├── cart.php              ← Session cart helpers
│   └── admin_auth.php        ← Admin session checks
├── templates/
│   ├── shop/                 ← Store HTML fragments & layout
│   │   ├── header.html
│   │   ├── footer.html
│   │   ├── page-index.html
│   │   ├── product-card.html
│   │   ├── product-buy-form.html
│   │   ├── page-cart.html
│   │   ├── cart-empty.html
│   │   ├── cart-filled.html
│   │   ├── cart-row.html
│   │   ├── page-checkout.html
│   │   ├── checkout-form.html
│   │   └── checkout-success.html
│   └── admin/                ← Admin HTML fragments & layout
│       ├── header.html
│       ├── footer.html
│       ├── login.html
│       ├── page-products.html
│       ├── product-row.html
│       ├── page-product-form.html
│       ├── page-orders.html
│       ├── order-row.html
│       ├── order-detail.html
│       └── order-item-row.html
├── css/
│   └── style.css
├── js/
│   ├── shop.js
│   └── admin.js
├── data/
│   ├── .gitignore            ← ignores shop.db
│   └── shop.db               ← created automatically (not in repo)
└── admin/
    ├── login.php
    ├── logout.php
    ├── index.php
    ├── product-form.php
    ├── product-delete.php
    └── orders.php
```

---

## 5. Database

SQLite file: **`data/shop.db`** (ensure **`data/`** exists and is writable).

| Table | Purpose |
|-------|---------|
| **products** | Catalog: name, description, price, image URL, stock, active flag. |
| **admins** | Admin username + password hash. |
| **orders** | One row per checkout: customer name, email, total, timestamp. |
| **order_items** | Line items: order id, product id, quantity, price at purchase time. |

Foreign keys are enabled in PHP via **`PRAGMA foreign_keys = ON`**. Product lines in old orders remain meaningful even if a product is deleted later (detail view uses a **LEFT JOIN**).

---

## 6. Security notes (teaching points)

- **Output escaping:** Use **`h()`** for text that comes from the database or forms when it is placed into HTML (except in rare cases where you intentionally output trusted HTML).
- **Passwords:** Stored with **`password_hash()`** / **`password_verify()`**, not in plain text.
- **Admin routes:** Protected with **`admin_require_login()`**; always POST for destructive actions where applicable (for example delete product).
- **Stock:** Checkout runs in a **transaction** and checks stock when updating to reduce overselling in simple scenarios.

This stack is suitable for learning; a production site would add HTTPS, CSRF tokens, stricter validation, rate limiting, and more.

---

*End of documentation.*
