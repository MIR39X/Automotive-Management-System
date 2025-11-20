# AMS - Automotive Management System

## Overview
AMS is a small PHP/MySQL application that runs under a traditional LAMP stack (XAMPP in local development). It exposes a browser-based back office for managing vehicles, customers, employees, and the purchase history that ties those entities together. The codebase is deliberately framework-free: routing is handled with simple PHP files in `public/`, templating is done through shared `header.php` / `footer.php` includes, and styling lives in the `assets/css` folder.

## Current Status
- Vehicles, Customers, and Employees modules are fully wired, with CRUD views and purpose-built dashboards.
- Customer profiles now include inline purchase recording, coupon validation, inventory adjustments, and lifetime value stats.
- Vehicle images are uploaded to `assets/uploads/` (5 MB limit) and rendered throughout the site; there is no CDN or S3 dependency.
- Inventory and Coupon records are referenced from the customer purchase forms but must currently be seeded directly in the database (no UI yet).
- Session-backed authentication now protects every management screen. Unauthenticated visitors only see the live inventory grid on the landing page; KPIs and admin modules require a login.
- All CRUD endpoints were recently audited so that business logic/redirects execute before the layout renders, eliminating the "headers already sent" warnings that previously appeared after saving or deleting records.
- Add/edit forms now persist user input when validation fails, reducing the risk of data loss during long sessions.
- Menu links for Sales/Maintenance still point to placeholders (no code yet).

## Features
### Vehicle Inventory
- List, search (via browser find), and drill into vehicles with hero cards, KPIs, and a gallery view.
- Add/edit forms capture VIN, brand/model, year, status (`available`, `sold`, `service`), price, description, and an optional image with client-side preview and 5 MB server-side validation.
- Deleting a vehicle removes it from the catalog and exposes the "no vehicles yet" empty state on both the dashboard and landing page.
- Home page (`public/index.php`) surfaces a live inventory grid; authenticated users also see KPI tiles (total/available/sold/value).

### Customers & Purchases
- Customer list offers quick links into a detailed CRM-style profile.
- Profiles summarize contact info, total purchases, lifetime spend, and the most recent purchase date.
- Two purchase forms exist:
  - **Record Purchase** – quick workflow that infers unit price from the selected vehicle or inventory item, applies optional coupons, decrements product stock, and automatically flips the vehicle status to “sold.”
  - **Add Purchase** – manual override that lets an operator set explicit unit prices/discounts.
- Purchase history displays coupon usage, totals, and lets you delete individual entries.
- Available coupons are listed on the profile to limit context switching.

### Employee Directory
- Stores CNIC, DOB, salary, phone, role, and hire date data.
- List view shows aggregate KPIs (headcount, average salary, last hire) and links to profile pages.
- Add/edit forms enforce basic validation rules before persisting via PDO.

### Shared UI & Configuration
- `includes/header.php` defines the `$base` path used in all asset and navigation links—update it if you serve the project from a different directory.
- Global layout and typography sit in `assets/css/style.css`; landing page-specific styles are in `assets/css/index.css`.
- Database connectivity is centralized in `includes/db.php`, which also performs small boot-time migrations (ensuring `customer.notes`, `purchase`, and `employee` tables exist).
- The header now boots sessions, exposes `$isAuthenticated`, and enforces `$requireAuth = true` redirection logic. A new `public/login.php`/`logout.php` pair handles the authentication flow (demo credentials: `admin / admin123`).
- Guests only see the live inventory grid; navigation links for Vehicles/Employees/Customers provide a login prompt until the session is authenticated.

## Project Structure
```
ams_project/
├── assets/
│   ├── css/               Global + landing page styles
│   └── uploads/           Vehicle image uploads (writeable directory)
├── includes/
│   ├── db.php             PDO connection + lightweight migrations
│   ├── header.php         Shared head/nav + base path config
│   └── footer.php         Shared footer markup
├── public/
│   ├── index.php          Landing page (vehicle grid)
│   ├── vehicles/          Vehicle CRUD + detail views
│   ├── customers/         Customer CRUD + CRM view + purchase tooling
│   └── employees/         Employee CRUD + profile view
└── README.md
```

## Getting Started
### Requirements
- PHP 8.1+ with the `pdo_mysql` extension enabled
- MySQL 5.7+ (or MariaDB equivalent)
- Apache/Nginx or XAMPP/Laragon for local hosting

### Installation Steps
1. Clone or copy this folder into your web root (e.g., `C:\xampp\htdocs\ams_project`).
2. Ensure the `$base` constant near the top of `includes/header.php` matches the folder name or virtual host you will use.
3. Update the MySQL credentials in `includes/db.php` if you are not using the default `root` / blank password combo.
4. Create a database named `ams_db` and load the schema below. It covers every table that the current PHP code expects, including the inventory/coupon tables that are referenced from the UI but do not have dedicated CRUD screens yet:

```sql
CREATE DATABASE IF NOT EXISTS ams_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ams_db;

CREATE TABLE IF NOT EXISTS vehicle (
  id INT AUTO_INCREMENT PRIMARY KEY,
  vin VARCHAR(50) UNIQUE,
  brand VARCHAR(120) NOT NULL,
  model VARCHAR(120) NOT NULL,
  year INT NULL,
  price DECIMAL(12,2) NULL,
  status ENUM('available','sold','service') DEFAULT 'available',
  description TEXT NULL,
  image VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS customer (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  phone VARCHAR(50) NULL,
  email VARCHAR(255) NULL,
  address TEXT NULL,
  notes TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS inventory (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  qty INT NOT NULL DEFAULT 0,
  unit_price DECIMAL(10,2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS coupon (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(50) NOT NULL UNIQUE,
  discount_type ENUM('percent','flat') NOT NULL DEFAULT 'percent',
  discount_value DECIMAL(10,2) NOT NULL,
  min_purchase DECIMAL(10,2) NOT NULL DEFAULT 0,
  valid_from DATE NULL,
  valid_to DATE NULL,
  uses_allowed INT NOT NULL DEFAULT 0,
  uses_count INT NOT NULL DEFAULT 0,
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS employee (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  cnic VARCHAR(20) NOT NULL,
  role VARCHAR(120) NOT NULL,
  phone VARCHAR(50) NOT NULL,
  hire_date DATE NOT NULL,
  dob DATE NOT NULL,
  salary DECIMAL(12,2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS purchase (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT NOT NULL,
  vehicle_id INT NULL,
  inventory_id INT NULL,
  qty INT NOT NULL,
  unit_price DECIMAL(10,2) NOT NULL,
  discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
  coupon_id INT NULL,
  total_amount DECIMAL(10,2) NOT NULL,
  notes TEXT NULL,
  purchase_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_purchase_customer FOREIGN KEY (customer_id) REFERENCES customer(id),
  CONSTRAINT fk_purchase_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicle(id),
  CONSTRAINT fk_purchase_inventory FOREIGN KEY (inventory_id) REFERENCES inventory(id),
  CONSTRAINT fk_purchase_coupon FOREIGN KEY (coupon_id) REFERENCES coupon(id)
) ENGINE=InnoDB;
```

5. Give your web server write access to `assets/uploads/` so vehicle photos can be stored.
6. Start Apache/MySQL (e.g., via XAMPP) and browse to `http://localhost/ams_project/public/index.php`.

### Usage Notes
- Seed a few vehicles first so the landing page and vehicle KPIs are populated.
- Seed at least one inventory item and coupon directly in MySQL if you want to exercise the purchase workflow (there is no admin UI yet).
- Every customer detail page exposes purchase logging and coupon validation. Deleting a purchase through the UI removes it from the `purchase` table but does not currently roll back inventory or coupon usage counts—handle reversals manually if needed.
- Vehicle status automatically flips to `sold` when the purchase form links it to a customer. Editing the vehicle later lets you override status back to `available`.
- Default login credentials live inside `public/login.php` (`admin/admin123`). Update or replace this bootstrap logic if you need stronger authentication.

## Roadmap / Known Gaps
- Sales, maintenance, and authentication pages are placeholders only—their navigation links will 404 until those modules are implemented.
- There are no automated tests or deployment scripts; everything runs directly in PHP.
- Inventory and coupon CRUD screens would make the purchase flow smoother and reduce the need for direct SQL updates.
- Guest mode is intentionally limited to the live inventory grid; future iterations may expose additional read-only modules without authentication.

Feel free to open issues or submit pull requests if you extend the application—especially around the missing modules noted above.
