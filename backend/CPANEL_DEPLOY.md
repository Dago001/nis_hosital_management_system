# cPanel Deployment Guide (PostgreSQL)

Step-by-step deploy of the NIS Medical Services HMS to shared **cPanel** hosting
with **PostgreSQL**. Assets are pre-built and committed (`public/build`), so you
do **not** need Node/npm on the server.

> General, host-agnostic notes live in `DEPLOYMENT.md`. This file is the
> cPanel-specific procedure.

---

## 0. Prerequisites (cPanel dashboard)

- **Select PHP Version** → choose **8.3 or 8.4**, then enable extensions:
  `pdo_pgsql`, `pgsql`, `mbstring`, `bcmath`, `openssl`, `ctype`, `fileinfo`,
  `curl`, `gd`, `zip`.
- **PostgreSQL Databases** icon present. ✅
- **Terminal** (SSH) — strongly preferred. If unavailable, see
  "Appendix: No SSH / Terminal" at the end.

## 1. Create the database (cPanel → PostgreSQL Databases)

1. Create a database (cPanel prefixes it, e.g. `cpuser_hms`).
2. Create a database user with a strong password (e.g. `cpuser_admin`).
3. Add the user to the database with **ALL PRIVILEGES**.
4. Note the exact **prefixed** names — you'll need them in `.env`.

## 2. Get the code onto the server (Terminal)

```bash
cd ~
git clone https://github.com/Dago001/nis_hosital_management_system.git app
cd app/backend
composer install --no-dev --optimize-autoloader
```

## 3. Point the domain at Laravel's `public/`

cPanel serves the domain from `public_html`. Choose one:

- **Preferred:** cPanel → **Domains** → set the site's **Document Root** to
  `app/backend/public`.
- **If document root cannot be changed:** move the contents of
  `app/backend/public/` into `public_html/`, keep the rest of `app/backend/`
  **outside** the web root, then edit `public_html/index.php` so its two
  `require` lines point to the real `vendor/autoload.php` and
  `bootstrap/app.php` locations.

Never expose anything but `public/` to the web (the `.env` and `storage/` must
stay out of the document root).

## 4. Environment

```bash
cp .env.production.example .env
php artisan key:generate
```

Edit `.env` and set at least:

```
APP_URL=https://yourdomain.gov.ng
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=cpuser_hms        # exact prefixed DB name
DB_USERNAME=cpuser_admin      # exact prefixed user
DB_PASSWORD=********
SESSION_DOMAIN=yourdomain.gov.ng
SANCTUM_STATEFUL_DOMAINS=yourdomain.gov.ng
MAIL_MAILER=smtp
MAIL_HOST=...                 # your SMTP host
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS=no-reply@yourdomain.gov.ng
SEED_PASSWORD=<strong-password>   # used by db:seed for staff accounts
```

Keep `APP_DEBUG=false` and `APP_ENV=production` (already set in the template).

## 5. Migrate, storage link, cache

```bash
php artisan migrate --force
php artisan storage:link          # see note below if symlinks are blocked
php artisan db:seed --force       # optional: seeds roles/permissions + staff
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

If `storage:link` fails (some shared hosts block symlinks), instead copy the
public assets after each deploy:

```bash
rm -rf public/storage
cp -r storage/app/public public/storage
```

## 6. File permissions

```bash
chmod -R 775 storage bootstrap/cache
```

## 7. HTTPS

cPanel → **SSL/TLS Status** → run **AutoSSL** for the domain (Let's Encrypt).
The app forces secure, encrypted cookies, so it must be served over HTTPS.

## 8. First-login hardening

- Log in as a seeded admin (`admin@immigration.gov.ng`) using `SEED_PASSWORD`.
- Immediately change passwords for all seeded accounts, or delete demo accounts
  you don't need, via the **User Accounts** admin screen.
- Confirm the patient portal loads at `/portal`.

## 9. Verify

```bash
php artisan about     # env=production, debug=false, config cached
php artisan test      # optional sanity check
```

Then browse the site over HTTPS and log in.

---

## Updating the app later

```bash
cd ~/app && git pull origin main        # or your deployed branch
cd backend
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache route:cache view:cache
# if using the copy workaround: cp -r storage/app/public public/storage
```

Assets are committed, so no build step is required on the server. If you change
front-end assets, run `npm run build` **locally**, commit `public/build`, and
`git pull` on the server.

---

## Appendix: No SSH / Terminal

If the host has no Terminal:

1. Build locally: `composer install --no-dev` and ensure `public/build` is
   present, then upload the whole `backend/` folder via File Manager / FTP
   (including `vendor/`).
2. Create the `.env` in File Manager (paste from `.env.production.example`) and
   set an `APP_KEY`: generate one locally with `php artisan key:generate --show`
   and paste it in.
3. Run migrations without a shell using cPanel **Cron Jobs** (one-off):
   `cd ~/app/backend && php artisan migrate --force && php artisan db:seed --force`
   then remove the cron entry.
4. Symlinks are usually blocked here — use the `cp -r storage/app/public
   public/storage` workaround from step 5.
