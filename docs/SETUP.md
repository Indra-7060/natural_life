# Setup guide — Sugarlif / Natural Life (CodeIgniter 4)

## 1. Requirements

| Item | Version | Notes |
|---|---|---|
| PHP | 8.2 or newer | Extensions: `intl`, `mbstring`, `mysqli`, `gd` (with WebP), `curl`, `openssl`, `fileinfo` |
| MySQL / MariaDB | MySQL 5.7+ / MariaDB 10.4+ | Shipped with XAMPP |
| XAMPP | 8.2+ | Apache is optional — the built-in PHP server is enough for local work |

**XAMPP note:** XAMPP's PHP ships with `intl` switched off, and CodeIgniter needs it. Open `C:\xampp\php\php.ini`, find `;extension=intl`, remove the leading `;`, save, and restart Apache. Check with `C:\xampp\php\php.exe -m` — `intl` should be listed.

## 2. Install (first time)

Run these from the project folder (`C:\natural-life-in`). If `php` is not on your PATH, use `C:\xampp\php\php.exe` instead of `php`.

1. **Start MySQL** in the XAMPP Control Panel.
2. **Create the database** — in phpMyAdmin (`http://localhost/phpmyadmin`) create `sugarlif_db` with collation `utf8mb4_unicode_ci`, or run:
   ```
   C:\xampp\mysql\bin\mysql.exe -uroot -e "CREATE DATABASE sugarlif_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
   ```
3. **Create the environment file** — copy `.env.example` to `.env`, then edit `.env`:
   - database name / user / password (XAMPP default: `root`, empty password);
   - `admin.defaultEmail` and `admin.defaultPassword` — the first administrator's sign-in (choose your own password, 10+ characters);
   - run `php spark key:generate` to fill `encryption.key`.
4. **Install PHP dependencies** (only if the `vendor/` folder is missing): `composer install --no-dev`
5. **Create the tables:** `php spark migrate`
6. **Load the website content:** `php spark db:seed DatabaseSeeder`
   This imports the 7 products, 9 pack sizes, 16 pages, 6 blog posts, FAQs, testimonials, team, navigation, settings and redirects, copies the product photographs into `public/uploads/media/` and generates optimised image sizes. It takes 20–40 seconds.
7. **Start the site:** `php spark serve --port 8080`
8. Open **http://localhost:8080/** (website) and **http://localhost:8080/admin** (admin panel).

> A `.env` for this machine has already been created during development. Its admin password is in the line `admin.defaultPassword` of `C:\natural-life-in\.env`.

## 3. Admin login

- Address: `http://localhost:8080/admin`
- Email and password: the `admin.defaultEmail` / `admin.defaultPassword` values that were in `.env` when you ran the seeder.
- At first sign-in you are taken to **My profile** and must set a new password before anything else. After that the two `admin.default*` lines can be deleted from `.env`.
- Add more administrators in **Admin → Admin users**, or from the command line (the password is typed at a prompt):
  ```
  php spark admin:create someone@example.com "Their Name" store-manager
  ```
  The same command resets a forgotten password. Roles: `super-admin`, `store-manager`, `content-editor`.

## 4. Running under XAMPP Apache instead of `spark serve`

The web root must be the project's **`public`** folder — never the project root.

1. In `C:\xampp\apache\conf\extra\httpd-vhosts.conf` add:
   ```apache
   Listen 8080
   <VirtualHost *:8080>
       DocumentRoot "C:/natural-life-in/public"
       <Directory "C:/natural-life-in/public">
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```
2. Make sure `mod_rewrite` is enabled in `httpd.conf` (it is by default in XAMPP) and restart Apache.
3. Keep `app.baseURL = 'http://localhost:8080/'` in `.env`. If you use another address, change `app.baseURL` to match exactly (with the trailing slash).

## 5. Starting again from a clean database

```
php spark migrate:refresh -f
php spark db:seed DatabaseSeeder
```
This wipes **everything**, including orders and customers. The content seeders never overwrite an existing catalogue: on a database that already has products they skip themselves.

## 6. Going live — checklist

| Step | Where |
|---|---|
| `CI_ENVIRONMENT = production` | `.env` |
| `app.baseURL` = the real `https://` address; `app.forceGlobalSecureRequests = true` | `.env` |
| New `encryption.key` (`php spark key:generate`) | `.env` |
| A dedicated MySQL user with a strong password (not `root`) | `.env` |
| CCAvenue Merchant ID, Access Code, Working Key, `ccavenue.mode = live` | `.env` — see `MISSING-CONFIGURATION.md` |
| In the CCAvenue dashboard, register the site's domain and these URLs: redirect `https://YOUR-DOMAIN/payment/ccavenue/response`, cancel `https://YOUR-DOMAIN/payment/ccavenue/cancel` | CCAvenue merchant panel |
| SMTP details | `.env` |
| Web root points at `public/`; `writable/` and `public/uploads/` are writable by PHP | Hosting |
| Terms & Conditions text, refund-policy placeholders, certificates, GSTIN / FSSAI | Admin panel — see `analysis/09-missing-information.md` |
| Point the domain, then submit `https://YOUR-DOMAIN/sitemap.xml` in Google Search Console | — |

`.env` is listed in `.gitignore` and must never be committed.

## 7. Where things are

| You want to change… | Go to |
|---|---|
| Products, prices, pack sizes, stock, photos | Admin → Products |
| Home page text and sections | Admin → Home page; hero slides in Admin → Home banners |
| Any other page | Admin → Pages → open the page → Content sections |
| Menus | Admin → Navigation |
| Shipping charge / free-shipping threshold | Admin → Settings → Shop & shipping |
| Phone, e-mail, address, logo, footer | Admin → Settings |
| Redirect an old address | Admin → Redirects |
| Colours and typography | `public/assets/css/site.css` (design tokens at the top) |
| Page layouts | `app/Views/` — one view per page template |

## 8. Re-running the checks

With the site running on port 8080:
```
bash docs/tools/smoke.sh                 # every public URL
php docs/tools/functional-test.php       # 127 end-to-end checks (creates and removes its own test data)
```
`docs/tools/functional-test.php` is for local development only: it briefly adds dummy CCAvenue test keys to `.env` to exercise the payment-callback checks and restores the file afterwards.
