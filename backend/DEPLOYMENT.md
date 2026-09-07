# NIS Medical Services — Production Deployment & Go-Live Checklist

This guide covers hardening and deploying the Hospital Management System for
production use with real patient data (PHI).

## 1. Environment configuration

```bash
cp .env.production.example .env
php artisan key:generate          # sets a fresh APP_KEY
```

Then edit `.env` and set real values. **Critical settings** (already defaulted
safely in the template):

| Setting | Production value | Why |
|---|---|---|
| `APP_ENV` | `production` | Disables dev-only behaviour |
| `APP_DEBUG` | `false` | `true` leaks stack traces & config to attackers |
| `APP_URL` | your HTTPS URL | Correct links, cookies, assets |
| `DB_CONNECTION` | `pgsql` | PostgreSQL for real workloads (not SQLite) |
| `SESSION_ENCRYPT` | `true` | Encrypts session payloads at rest |
| `SESSION_SECURE_COOKIE` | `true` | Cookie only sent over HTTPS |
| `SESSION_DOMAIN` / `SANCTUM_STATEFUL_DOMAINS` | your domain | Scopes auth cookies correctly |
| `MAIL_MAILER` | `smtp` (+ real creds) | Required for appointment reminder emails to actually send |
| `SEED_PASSWORD` | strong value, or remove demo users | Demo accounts must not ship with the known dev password |

## 2. Build & migrate

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan storage:link          # public symlink for patient photos
npm install --ignore-scripts && npm run build
php artisan config:cache route:cache view:cache
```

> `composer setup` runs install → key:generate → migrate → **storage:link** →
> npm build for you.

## 3. Demo / seed accounts

The seeder creates staff accounts (`admin@immigration.gov.ng`, etc.). Before
go-live **either**:

- set a strong `SEED_PASSWORD` in `.env` and re-seed a clean DB, **or**
- do not run `db:seed` in production and create real accounts through the
  User Accounts admin screen, **or**
- rotate every seeded account's password immediately after first login.

Never leave the `Password123#` dev default reachable in production.

## 4. File storage & backups

- `php artisan storage:link` must succeed (patient passport photos are served
  from `public/storage`).
- Patient **documents** are stored on the private `local` disk
  (`storage/app/private/patient_documents/…`) and streamed only through the
  authenticated, audit-logged download route — never served publicly.
- Back up **both** the database and the `storage/` directory on a schedule.
  Test a restore.

## 5. Web server & TLS

- Terminate TLS (HTTPS) at your reverse proxy / load balancer; redirect HTTP→HTTPS.
- Point the web root at `public/` only.
- Set correct ownership/permissions on `storage/` and `bootstrap/cache/`
  (writable by the web user).

## 6. Background processing

Appointment reminder emails and other queued work use `QUEUE_CONNECTION=database`.
Run a worker under a process manager (systemd / supervisor):

```bash
php artisan queue:work --tries=3 --timeout=90
```

## 7. Access control notes

- RBAC is enforced by the `role_or_permission` middleware; `super_admin` and
  `ict_admin` bypass checks by design.
- **Per-doctor isolation:** a doctor can only open/complete a consultation for a
  patient assigned to them (via triage). Senior clinical roles — `super_admin`,
  `medical_director`, `chief_medical_officer` — may access any visit
  (break-glass), and every access is written to the audit trail.
- Reading a patient file (`view_patients`) is broadly available to clinical
  staff on a need-to-know basis and is audit-logged rather than blocked.

## 8. Pre-launch verification

```bash
php artisan test            # full suite must pass
php artisan about           # confirm env=production, debug=false, cached
```

- [ ] `APP_DEBUG=false`, `APP_ENV=production`
- [ ] HTTPS enforced, secure cookies on
- [ ] PostgreSQL connected, migrations applied
- [ ] `storage:link` created
- [ ] Demo passwords rotated / removed
- [ ] Real SMTP configured and a test reminder received
- [ ] Queue worker running
- [ ] DB + storage backups scheduled and a restore tested
