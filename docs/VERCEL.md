# Deploying to Vercel

The site can run on Vercel, with the limits listed below. Read **"What you should know first"** before choosing this route: ordinary PHP hosting (see `DEPLOYMENT.md`) remains the simpler and better-tested option for this shop.

## What you should know first

| Point | Detail |
|---|---|
| **Not tested on Vercel itself** | No Vercel deployment was made: it needs an external database, which was not available. The Vercel mode was tested on the development machine only (see the last section). Expect to fix small things on the first deploy. |
| **PHP on Vercel is unofficial** | Vercel has no PHP support of its own. This uses the community runtime `vercel-php` (version 0.7.4, PHP 8.3). Its repository is now archived, so it will not receive updates. |
| **Vercel does not host MySQL** | You need a MySQL / MariaDB database elsewhere that accepts connections from the internet (for example Aiven, Railway, TiDB Cloud, or remote MySQL on a hosting account). |
| **Commercial use needs a paid Vercel plan** | Vercel's free Hobby plan is for personal, non-commercial projects. A shop that takes payments belongs on the Pro plan. |
| **New image uploads are not optimised** | The runtime has no `gd` image extension. Images uploaded in the admin on Vercel are stored and shown as uploaded, without the smaller WebP versions. The images already in the site keep their optimised versions. |
| **Uploads are limited to 4 MB** | Vercel refuses larger requests. |
| **No disk** | Vercel's disk is read-only. Sessions, cache and uploaded images are kept in the database instead (tables `ci_sessions`, `cache_store`, `media_files`). Uploaded images are delivered by PHP from the database, which is slower than a file. |
| **E-mail needs SMTP** | Without SMTP details, e-mails are thrown away on Vercel (on normal hosting they are saved to a folder). |

Nothing changes for ordinary hosting or for local work: the database mode switches on only when Vercel's own `VERCEL` variable is present.

## Files added for Vercel

| File | Purpose |
|---|---|
| `vercel.json` | Runtime, region (`sin1`, Singapore, matching the TiDB database) and routing: files in `public/` are served directly, everything else goes to PHP. |
| `api/index.php` | Entry point. Passes the request to the normal `public/index.php`. |
| `api/php.ini` | Upload size and error settings. |
| `.vercelignore` | Keeps `.env`, `vendor/`, `docs/`, `deploy/`, tests and local scratch files out of the upload. |

## Steps

### 1. Create the database and import the data

1. Create a MySQL 5.7+/8 or MariaDB 10.4+ database with your provider, in or near Singapore (the site runs in Vercel's Singapore region; change `regions` in `vercel.json` if your database is elsewhere).
2. Import `deploy/sugarlif_db.sql` into it, using the provider's import tool or any MySQL client. It creates 42 tables.

   The file is produced by `php docs/tools/build-deploy.php` (MySQL must be running locally). Vercel cannot run the migration commands, so importing this file is the only way to create the tables.
3. Note the host, port, database name, user and password. If the provider gives you a CA certificate, keep it too.

### 2. Put the project on Vercel

Either connect a Git repository in the Vercel dashboard (*Add New → Project*), or use the command line from the project folder:

```
npm install -g vercel
vercel login
vercel
```

When asked, keep the detected settings (no framework, no build command). In *Project → Settings → Build and Deployment* set **Node.js Version to 22.x**, which the PHP runtime requires.

### 3. Add the environment variables

*Project → Settings → Environment Variables*. Vercel names cannot contain dots, so use underscores exactly as written here.

Required:

| Name | Value |
|---|---|
| `database_default_hostname` | Database host |
| `database_default_port` | Database port (often `3306`) |
| `database_default_database` | Database name |
| `database_default_username` | Database user |
| `database_default_password` | Database password |
| `encryption_key` | A fresh key. Create one with: `php -r "echo 'hex2bin:'.bin2hex(random_bytes(32));"` |

If the database requires an encrypted connection (most cloud databases do):

| Name | Value |
|---|---|
| `DB_SSL` | `true` |
| `DB_SSL_CA` | The provider's CA certificate, pasted as text (the whole `-----BEGIN CERTIFICATE-----` block). Leave out if the provider uses a public certificate. |
| `DB_SSL_VERIFY` | `false` only if the connection fails and you cannot obtain the CA certificate. |

When you have them:

| Name | Value |
|---|---|
| `app_baseURL` | Needed once you add your own domain: `https://natural-life.in/` (with the closing slash). Without it the site uses its `vercel.app` address. |
| `ccavenue_merchantId`, `ccavenue_accessCode`, `ccavenue_workingKey` | From the CCAvenue merchant panel. Until set, customers cannot pay and no order is marked paid. |
| `ccavenue_mode` | `test` or `live` |
| `email_SMTPHost`, `email_SMTPUser`, `email_SMTPPass`, `email_SMTPPort`, `email_SMTPCrypto` | SMTP details. Until set, no e-mail is sent. |
| `email_fromEmail`, `email_fromName` | Sender shown on e-mails |

Never upload your local `.env`; `.vercelignore` already excludes it.

### 4. Deploy and check

```
vercel --prod
```

Then:

1. Open the site: the home page should show with images.
2. Open `/admin/login` and sign in with `admin@natural-life.in` and the admin password from your local `.env` (`admin.defaultPassword`). You must choose a new password immediately.
3. Add a product to the cart, open the cart on a second page (confirms sessions work).
4. Upload a small image in *Admin → Media* and check it displays.
5. If a page shows an error, open *Vercel → Project → Logs*: the application writes its errors there.

### 5. After go-live

- Add your domain in *Project → Settings → Domains*, then set `app_baseURL` and redeploy.
- In the CCAvenue merchant panel register the domain, redirect URL `https://YOUR-DOMAIN/payment/ccavenue/response` and cancel URL `https://YOUR-DOMAIN/payment/ccavenue/cancel`.
- Place one real low-value order and confirm it shows as paid in *Admin → Orders*.

## If something goes wrong

| What you see | Likely cause |
|---|---|
| Build fails mentioning the runtime or Node | Node.js version is not 22.x, or the runtime version in `vercel.json` is no longer available. |
| Every page shows a database error | A database variable is wrong, the database does not accept outside connections, or it needs `DB_SSL=true`. |
| "Table ... doesn't exist" | `sugarlif_db.sql` was not imported, or an older copy without the three new tables was used. |
| Styles and images missing | A custom domain was added without setting `app_baseURL`. |
| Signed out on every click, or cart empties | The `ci_sessions` table is missing. |
| Upload fails | The image is larger than 4 MB. |

## What was and was not tested

Tested on the development machine with `php docs/tools/serverless-test.php`, which starts the site in the same mode Vercel uses (read-only-host mode, every request passing through `api/index.php` with the routing of `vercel.json` imitated). 27 checks, all passing:

- Home, shop, blog, contact, cart, sitemap and admin sign-in pages load; static files are served without PHP; unknown addresses give the styled 404 page.
- A cart survives between requests with the session stored in `ci_sessions`.
- Settings and menus are cached in `cache_store`.
- The admin sign-in limit works through the database (blocked on the 7th wrong attempt).
- An admin signs in, uploads a 2.3 MB image, it is stored in `media_files` in 10 pieces, served back byte-for-byte identical, then deleted.
- Nothing is written to `writable/` or `public/uploads/` during all of the above.

After the changes the normal mode was rechecked: 127 of 127 functional checks and 48 of 48 public addresses.

Not tested:

- **Anything on Vercel**: the build, `vercel.json` routing, the PHP runtime, Composer install during the build, reading the environment variables, the automatic site address, and behaviour behind Vercel's network.
- **An external or encrypted database connection** (`DB_SSL`, `DB_SSL_CA`). The local database has no encryption, so this code has never run.
- Uploading without the `gd` extension (the local PHP has it). The code skips the optimised versions when `gd` is missing, but that path was not run.
- Checkout, payment and e-mail in this mode.
