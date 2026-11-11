# Deploying to PHP + MySQL hosting (cPanel type)

This guide puts the store on ordinary shared hosting. Everything you upload is in the `deploy/` folder:

| File | What it is |
|---|---|
| `deploy/sugarlif-upload.zip` | The site. Contains two folders: `sugarlif` (the application) and `public_html` (the web root). |
| `deploy/sugarlif_db.sql` | The database: 42 tables with all products, pages, articles, menus, settings and redirects. No orders, customers, carts or sessions. |

To rebuild both files after any change (MySQL must be running):

```
php docs/tools/build-deploy.php
```

## Before you start

- **natural-life.in is a live WordPress shop.** Uploading this site to the same `public_html` replaces it. Take a full backup of the current site (files and database) in the hosting panel first.
- **Recommended: go live on a test address first**, for example a subdomain such as `new.natural-life.in`, check it, and only then switch the main domain.
- The hosting needs **PHP 8.2 or newer** with the extensions `intl`, `mbstring`, `mysqli`, `gd` (with WebP), `curl`, `openssl`, and **MySQL 5.7+ or MariaDB 10.4+**. In cPanel these are set under *Select PHP Version*.
- An **SSL certificate** must be active on the address (cPanel: *SSL/TLS Status* → *Run AutoSSL*). The site redirects every visitor to `https://`.

## Steps

### 1. Create the database

cPanel → *MySQL Databases*:

1. Create a database (for example `youruser_sugarlif`).
2. Create a database user with a strong password.
3. Add the user to the database with **All privileges**.

Note the database name, user name and password for step 4.

### 2. Import the data

cPanel → *phpMyAdmin* → click the new database in the left column → *Import* → choose `sugarlif_db.sql` → *Import*.

It should finish with 42 tables.

### 3. Upload the files

cPanel → *File Manager*:

1. Go to your **home folder** (the folder that *contains* `public_html`, not `public_html` itself).
2. Upload `sugarlif-upload.zip` there and choose *Extract*.
3. You now have:
   - `sugarlif/` next to `public_html/` — the application. It is deliberately outside the web root so nobody can download its files.
   - `public_html/` — filled with `index.php`, `.htaccess`, `assets/` and `uploads/`.
4. Delete the zip from the server.

If `public_html` already held another site, remove its old files first (after the backup), otherwise old and new files are mixed.

**Using a subdomain or add-on domain?** Its web root is a different folder (for example `new.natural-life.in/`). Move the *contents* of the extracted `public_html/` into that folder. Then open `index.php` there and make sure this line points from that folder to the `sugarlif` folder:

```php
require FCPATH . '../sugarlif/app/Config/Paths.php';
```

For a web root one level deeper (such as `public_html/new/`) it becomes `'../../sugarlif/app/Config/Paths.php'`.

### 4. Fill in the configuration

In File Manager switch on *Settings → Show Hidden Files*, then edit `sugarlif/.env`. Replace every `CHANGE_ME`:

| Line | Value |
|---|---|
| `app.baseURL` | The full address with `https://` and a closing slash, e.g. `'https://natural-life.in/'` |
| `database.default.database` | Database name from step 1 |
| `database.default.username` | Database user from step 1 |
| `database.default.password` | That user's password, inside the quotes |

Leave `CI_ENVIRONMENT = production` and the `encryption.key` line as they are.

Fill these in when you have them (the site runs without them, with the limits shown):

| Lines | Until they are filled in |
|---|---|
| `ccavenue.merchantId`, `ccavenue.accessCode`, `ccavenue.workingKey`, then `ccavenue.mode = live` | Customers cannot pay. Orders are saved as "pending payment" and are never marked paid. |
| `email.SMTPHost`, `email.SMTPUser`, `email.SMTPPass` (and port / crypto if different) | No e-mail is sent. Order confirmations, password resets and enquiry notices are only saved as files in `sugarlif/writable/emails/`. |

### 5. Check folder permissions

`sugarlif/writable/` (and everything in it) and `public_html/uploads/` must be writable by PHP. On most cPanel hosts this is already the case (folders `755`). If the site shows an error about the cache or session folder, set these folders to `755`, or `775` if the host requires it.

### 6. First sign-in

1. Open `https://YOUR-DOMAIN/` — the home page should load with images.
2. Open `https://YOUR-DOMAIN/admin/login` and sign in with `admin@natural-life.in` and the admin password from your local `.env` (`admin.defaultPassword`).
3. You are asked to choose a new password straight away. Do this immediately: until then the live site is protected only by the starting password.

### 7. After go-live

- In the CCAvenue merchant panel register the domain and these two addresses:
  - redirect URL `https://YOUR-DOMAIN/payment/ccavenue/response`
  - cancel URL `https://YOUR-DOMAIN/payment/ccavenue/cancel`
- Place one real low-value order and confirm it shows as paid in *Admin → Orders*, and that the confirmation e-mail arrives.
- Old WordPress addresses are redirected automatically (12 redirects, see `redirect-map.md`). Submit `https://YOUR-DOMAIN/sitemap.xml` in Google Search Console.
- Complete the content still marked as missing: see `MISSING-CONFIGURATION.md` and `analysis/09-missing-information.md`.

## If something goes wrong

| What you see | Likely cause |
|---|---|
| Blank page or "500" | PHP older than 8.2, or the `intl` extension is off. Check *Select PHP Version*. Details are written to `sugarlif/writable/logs/`. |
| "Unable to connect to the database" | Wrong name, user or password in `.env`, or the user was not added to the database. Many hosts prefix names with the account name (`youruser_...`). |
| Home page works, every other page gives "404 Not Found" from the server | `public_html/.htaccess` is missing (hidden files were not extracted) or `mod_rewrite` is off. |
| Page loads without styling or images | `app.baseURL` does not match the address in the browser (check `https://`, `www` and the closing slash). |
| "Too many redirects" | SSL is not active yet, or the host sits behind a proxy (for example Cloudflare "Flexible" SSL). Use "Full" SSL, or temporarily put `#` in front of `app.forceGlobalSecureRequests`. |
| Images cannot be uploaded in the admin | `public_html/uploads/` is not writable, or `gd` has no WebP support. |

To see the full error on screen for a moment, change `CI_ENVIRONMENT` to `development` in `.env`, reload, then **change it back to `production`**.

## What was and was not tested

Tested on the development machine (Windows, PHP 8.3, MariaDB 10.4):

- The zip was unpacked into an empty folder and the SQL file imported into a new empty database: 42 tables, 326 rows, row counts identical to the working database. The file leaves out carts, sessions, cache and password-reset requests, and switches on the forced password change for the first administrator.
- The unpacked copy was served from its `public_html` folder with the application in the neighbouring `sugarlif` folder. Home, shop, a product page, blog, cart, contact, admin sign-in page, sitemap, robots file, a stylesheet and a product image all loaded; checkout redirected as expected with an empty cart; an unknown address returned the styled 404 page; no page linked back to `localhost`.
- A scan for names that work on Windows but fail on Linux (letter-case differences): 139 view references, 134 classes, 12 database-named page templates and 41 media records checked, no mismatches.
- The packaged `.env` contains no password and no development values.

Not tested:

- **An actual upload to a hosting account.** No hosting login was available, so nothing was tested on Linux, Apache / LiteSpeed, cPanel or over HTTPS. The `.htaccess` rules and the HTTPS redirect are the standard CodeIgniter ones and are untested on a real host.
- Importing the SQL file through phpMyAdmin or into MySQL 8 (it was imported with the command-line client into MariaDB 10.4).
- Signing in to the admin, placing an order or uploading an image on the unpacked copy.
- CCAvenue payments and SMTP delivery (no credentials), as before.
