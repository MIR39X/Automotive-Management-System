# README_GPT

This file is a condensed "context package" you (future Codex) can read before jumping back into the project. It captures the current architecture, auth rules, and UI conventions to minimize rediscovery time.

## High-Level Overview
- **Stack**: Plain PHP 8.x + MySQL (PDO) served via XAMPP. No framework; routing is file-based (`public/*.php`).
- **Structure**:
  - `includes/` houses `db.php` (PDO bootstrap + lightweight migrations), `header.php` (session boot, nav, `$base` path), `footer.php`.
  - `public/` contains module folders (`vehicles`, `customers`, `employees`) plus `index.php`, `login.php`, `logout.php`.
  - `assets/css/` holds shared styles (`style.css`, `index.css`); `assets/uploads/` stores vehicle images.
- **Core Modules**: Vehicles, Customers, Employees each have CRUD pages plus view dashboards with stats and styled cards.
- **Customers** have embedded purchase management referencing `vehicle`, `inventory`, and `coupon` tables (no UI yet for inventory/coupons—seed manually).
- **Vehicles** support image uploads (5 MB limit) with storage under `assets/uploads/`.

## Authentication / Access Flow
- `includes/header.php` now starts the session and exposes `$isAuthenticated`. Any page that sets `$requireAuth = true` before including the header will force-login via `/public/login.php`.
- `public/login.php` authenticates against demo creds (`admin / admin123`) and sets `$_SESSION['user']` with a timestamp. It supports redirect parameters so users return to their intended page.
- `public/logout.php` clears the session and sends the visitor back to the login screen.
- **Guest Mode**: Unauthenticated users can only see the live inventory grid on the landing page. KPIs, CRUD modules, and navigation entries for Vehicles/Customers/Employees remain hidden until logged in.

## UI/Styling Notes
- Global layout uses the container/topbar defined in `header.php`. The nav is a gradient pill; active links show the blue highlight.
- Module pages follow a pattern: hero section with stats/actions, responsive card/table layout, scoped `<style>` blocks within each page.
- Vehicle/Customer/Employee forms use rounded cards, uppercase labels, and consistent spacing. Remember to keep new forms/cards aligned with the existing CSS approach.

## Database Expectations
- `db.php` ensures the `customer.notes` column plus `purchase` and `employee` tables exist. Inventory/coupon tables are assumed to be present (see full schema in `README.md`).
- Purchase flows rely on `inventory`, `coupon`, and `vehicle` tables for joins/updates. If you add new modules, mirror the existing PDO pattern (prepared statements, `try/catch` for migrations).

## Working Notes / Gotchas
- `$base` in `header.php` is currently `/ams_project`. Update if the folder changes—many links rely on it.
- Image management: when editing vehicles, new uploads replace the old file and delete the previous image from storage.
- Coupon usage updates `uses_count` but deleting a purchase does **not** roll back inventory quantity or coupon usage; note in README if behavior changes.
- Guest view messaging lives in `public/index.php`. If you add more guest-access modules, adjust the conditional there.

## Suggested Future Tasks (carryover)
1. Build CRUD UI for `inventory` and `coupon` tables to avoid manual SQL seeding.
2. Implement Sales/Maintenance modules or hide their nav links until ready.
3. Replace demo auth with a real users table/password hashing.
4. Add CSRF protection + form tokens if security becomes a concern.
5. Centralize inline styles (optional) into CSS files once layouts stabilize.

When starting a new session, skim this file to reorient, then open:
1. `README.md` for full project description/schema.
2. `includes/header.php` + `public/login.php` to confirm auth flow.
3. One module file (e.g., `public/vehicles/list.php`) to refresh the UI pattern.

Keep this file updated whenever architecture materially changes so future work sessions are fast to spin up.
