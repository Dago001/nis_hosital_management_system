#!/usr/bin/env bash
#
# cPanel one-shot deploy helper for the NIS HMS (PostgreSQL).
# Run it from the Laravel app folder (the one containing `artisan`):
#
#     bash deploy_cpanel.sh            # install vendor + scaffold .env, then stop
#     # (edit .env with your DB + APP_URL, then:)
#     bash deploy_cpanel.sh --migrate  # migrate, seed, storage link, cache
#
# It handles the two shared-hosting gotchas automatically:
#   * uses the PHP 8.3 binary even when the shell default is older
#   * clears disabled functions (phpinfo/proc_open) just for Composer
#
set -euo pipefail

# --- Locate a PHP 8.3+ binary -------------------------------------------------
pick_php() {
    for c in \
        /opt/cpanel/ea-php83/root/usr/bin/php \
        /opt/cpanel/ea-php84/root/usr/bin/php \
        "$(command -v ea-php83 2>/dev/null || true)" \
        "$(command -v php83 2>/dev/null || true)" \
        "$(command -v php 2>/dev/null || true)"; do
        [ -n "$c" ] && [ -x "$c" ] || continue
        ver="$("$c" -r 'echo PHP_VERSION_ID;' 2>/dev/null || echo 0)"
        if [ "$ver" -ge 80300 ]; then echo "$c"; return 0; fi
    done
    return 1
}

PHP="$(pick_php)" || { echo "ERROR: no PHP 8.3+ binary found. Enable PHP 8.3 in cPanel first."; exit 1; }
echo ">> Using PHP: $PHP ($("$PHP" -r 'echo PHP_VERSION;'))"

# -d disable_functions= re-enables phpinfo/proc_open just for Composer runs.
COMPOSER_PHP="$PHP -d disable_functions="

if [ ! -f artisan ]; then
    echo "ERROR: run this from the folder containing 'artisan' (the Laravel app root)."
    exit 1
fi

# --- Step 1: vendor + .env ----------------------------------------------------
if [ ! -d vendor ] || [ ! -f vendor/autoload.php ]; then
    if [ ! -f composer.phar ]; then
        echo ">> Downloading Composer..."
        curl -sS https://getcomposer.org/installer | $COMPOSER_PHP
    fi
    echo ">> Installing PHP dependencies (no-dev)..."
    $COMPOSER_PHP composer.phar install --no-dev --optimize-autoloader --no-scripts
    echo ">> Discovering packages..."
    "$PHP" artisan package:discover --ansi
else
    echo ">> vendor/ already present, skipping Composer."
fi

if [ ! -f .env ]; then
    echo ">> Creating .env from production template..."
    cp .env.production.example .env
    "$PHP" artisan key:generate
    echo
    echo "============================================================"
    echo " .env created. EDIT IT NOW and set your real values:"
    echo "   - DB_DATABASE / DB_USERNAME / DB_PASSWORD (cPanel PostgreSQL)"
    echo "   - APP_URL, SESSION_DOMAIN, SANCTUM_STATEFUL_DOMAINS"
    echo "   - SEED_PASSWORD (a strong password)"
    echo "   - MAIL_* if you have SMTP"
    echo
    echo " Then run:  bash deploy_cpanel.sh --migrate"
    echo "============================================================"
    exit 0
fi

# --- Step 2: migrate / seed / link / cache (only with --migrate) --------------
if [ "${1:-}" != "--migrate" ]; then
    echo
    echo ">> vendor and .env are ready."
    echo ">> Review .env, then run:  bash deploy_cpanel.sh --migrate"
    exit 0
fi

echo ">> Running migrations..."
"$PHP" artisan migrate --force

echo ">> Seeding roles/permissions + staff accounts..."
"$PHP" artisan db:seed --force || echo "   (seed skipped or already applied)"

echo ">> Linking storage..."
if ! "$PHP" artisan storage:link 2>/dev/null; then
    echo "   symlink blocked; copying storage/app/public -> public/storage"
    rm -rf public/storage
    cp -r storage/app/public public/storage
fi

echo ">> Caching config/routes/views..."
"$PHP" artisan config:cache
"$PHP" artisan route:cache
"$PHP" artisan view:cache

chmod -R 775 storage bootstrap/cache 2>/dev/null || true

echo
echo ">> DONE. Verify with:  $PHP artisan about"
echo ">> Then set the domain's document root to this folder's /public and run AutoSSL."
