#!/usr/bin/env bash

set -Eeuo pipefail

APP_ROOT="${APP_ROOT:-$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)}"
DEPLOY_BRANCH="${DEPLOY_BRANCH:-master}"
APP_DOMAIN="${APP_DOMAIN:-siscom.sityoyitsolutions.com}"
APP_URL="https://${APP_DOMAIN}"
CONFIGURE_NGINX="${CONFIGURE_NGINX:-auto}"
CERTBOT_EMAIL="${CERTBOT_EMAIL:-admin@sityoyit.com}"

green='\033[0;32m'
yellow='\033[1;33m'
red='\033[0;31m'
reset='\033[0m'

log() { printf "${green}[SIS.COM]${reset} %s\n" "$*"; }
warn() { printf "${yellow}[PERINGATAN]${reset} %s\n" "$*"; }
fail() { printf "${red}[GAGAL]${reset} %s\n" "$*" >&2; exit 1; }

cd "$APP_ROOT"

for command_name in git php composer npm; do
    command -v "$command_name" >/dev/null 2>&1 || fail "Perintah ${command_name} tidak tersedia."
done

mkdir -p storage/framework/{cache,sessions,views} storage/logs

if command -v flock >/dev/null 2>&1; then
    deploy_lock="${TMPDIR:-/tmp}/siscom-deploy-${UID}.lock"
    exec 9>"$deploy_lock"
    flock -n 9 || fail "Deploy lain masih berjalan."
fi

maintenance_enabled=0
cleanup() {
    exit_code=$?

    if [[ "$maintenance_enabled" -eq 1 && -f artisan ]]; then
        php artisan up >/dev/null 2>&1 || true
    fi

    if [[ "$exit_code" -ne 0 ]]; then
        printf "${red}[SIS.COM] Deploy dihentikan pada baris %s.${reset}\n" "${BASH_LINENO[0]:-tidak diketahui}" >&2
    fi
}
trap cleanup EXIT

set_env_value() {
    local key="$1"
    local value="$2"

    ENV_FILE="$APP_ROOT/.env" ENV_KEY="$key" ENV_VALUE="$value" php -r '
        $file = getenv("ENV_FILE");
        $key = getenv("ENV_KEY");
        $value = getenv("ENV_VALUE");
        $contents = file_exists($file) ? file_get_contents($file) : "";
        $line = $key."=".$value;
        $pattern = "/^".preg_quote($key, "/")."=.*$/m";
        $contents = preg_match($pattern, $contents)
            ? preg_replace($pattern, $line, $contents)
            : rtrim($contents).PHP_EOL.$line.PHP_EOL;
        file_put_contents($file, $contents);
    '
}

run_as_root() {
    if [[ "$(id -u)" -eq 0 ]]; then
        "$@"
    elif command -v sudo >/dev/null 2>&1; then
        sudo "$@"
    else
        return 1
    fi
}

configure_nginx() {
    [[ "$CONFIGURE_NGINX" == "0" || "$CONFIGURE_NGINX" == "false" ]] && return 0
    command -v nginx >/dev/null 2>&1 || { warn "Nginx tidak ditemukan; konfigurasi domain dilewati."; return 0; }

    local nginx_target="/etc/nginx/sites-available/${APP_DOMAIN}"
    if [[ "$CONFIGURE_NGINX" == "auto" ]] && grep -Rqs "server_name ${APP_DOMAIN}" /etc/nginx 2>/dev/null; then
        log "Virtual host ${APP_DOMAIN} sudah tersedia."
        return 0
    fi

    local php_socket
    php_socket="$(find /run/php -maxdepth 1 -type s -name 'php*-fpm.sock' 2>/dev/null | sort -V | tail -n 1)"
    [[ -n "$php_socket" ]] || { warn "Socket PHP-FPM tidak ditemukan; konfigurasi Nginx dilewati."; return 0; }

    local temp_config
    temp_config="$(mktemp)"
    sed \
        -e "s|__DOMAIN__|${APP_DOMAIN}|g" \
        -e "s|__APP_ROOT__|${APP_ROOT}|g" \
        -e "s|__PHP_SOCKET__|${php_socket}|g" \
        deploy/nginx-siscom.conf > "$temp_config"

    run_as_root install -m 0644 "$temp_config" "$nginx_target" || {
        rm -f "$temp_config"
        warn "Tidak dapat memasang virtual host Nginx; jalankan deploy dengan hak sudo."
        return 0
    }
    rm -f "$temp_config"

    run_as_root ln -sfn "$nginx_target" "/etc/nginx/sites-enabled/${APP_DOMAIN}"
    run_as_root nginx -t
    run_as_root systemctl reload nginx
    log "Virtual host ${APP_DOMAIN} berhasil dipasang."

    if command -v certbot >/dev/null 2>&1; then
        local certbot_action="Meminta"
        local certbot_options=(
            --nginx
            --non-interactive
            --agree-tos
            --redirect
            --email "$CERTBOT_EMAIL"
            -d "$APP_DOMAIN"
        )

        if [[ -f "/etc/letsencrypt/live/${APP_DOMAIN}/fullchain.pem" ]]; then
            certbot_action="Memasang ulang"
            certbot_options+=(--reinstall)
        fi

        log "${certbot_action} sertifikat HTTPS untuk ${APP_DOMAIN}..."
        if run_as_root certbot "${certbot_options[@]}"; then
            log "HTTPS berhasil diaktifkan."
        else
            warn "Certbot belum berhasil. Pastikan DNS ${APP_DOMAIN} sudah mengarah ke VPS, lalu jalankan deploy kembali."
        fi
    fi
}

log "Memeriksa working tree..."
if ! git diff --quiet || ! git diff --cached --quiet; then
    git status --short --untracked-files=no
    fail "Ada perubahan pada file Git di server. Simpan perubahan tersebut sebelum deploy."
fi

log "Mengambil branch ${DEPLOY_BRANCH} terbaru..."
git fetch origin "$DEPLOY_BRANCH"
git checkout "$DEPLOY_BRANCH"
git pull --ff-only origin "$DEPLOY_BRANCH"

[[ -f .env ]] || { cp .env.example .env; warn ".env dibuat dari contoh; lengkapi database, email, dan Fonnte sebelum aplikasi digunakan."; }

set_env_value APP_NAME '"SIS.COM"'
set_env_value APP_ENV production
set_env_value APP_DEBUG false
set_env_value APP_URL "$APP_URL"
set_env_value APP_LOCALE id
set_env_value APP_FALLBACK_LOCALE en
set_env_value APP_TIMEZONE Asia/Jakarta
set_env_value ALLOW_PUBLIC_REGISTRATION false
set_env_value SESSION_DOMAIN "$APP_DOMAIN"
set_env_value SESSION_SECURE_COOKIE true

log "Memasang dan membangun aset frontend..."
npm ci --no-audit --no-fund
npm run build

log "Mengaktifkan mode pemeliharaan..."
php artisan down --retry=60 --refresh=15 || true
maintenance_enabled=1

log "Memasang dependency PHP produksi..."
composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

if ! grep -Eq '^APP_KEY=base64:.+' .env; then
    log "Membuat application key..."
    php artisan key:generate --force
fi

log "Memeriksa konfigurasi dan route..."
php artisan optimize:clear
php artisan route:list >/dev/null

if command -v mysqldump >/dev/null 2>&1; then
    backup_dir="$APP_ROOT/storage/app/backups"
    mkdir -p "$backup_dir"
    timestamp="$(date +%Y%m%d-%H%M%S)"

    db_json="$(php -r '
        require "vendor/autoload.php";
        $app = require "bootstrap/app.php";
        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        echo json_encode(config("database.connections.".config("database.default")));
    ')"

    db_host="$(DB_JSON="$db_json" php -r '$v=json_decode(getenv("DB_JSON"),true); echo $v["host"] ?? "127.0.0.1";')"
    db_port="$(DB_JSON="$db_json" php -r '$v=json_decode(getenv("DB_JSON"),true); echo $v["port"] ?? "3306";')"
    db_name="$(DB_JSON="$db_json" php -r '$v=json_decode(getenv("DB_JSON"),true); echo $v["database"] ?? "";')"
    db_user="$(DB_JSON="$db_json" php -r '$v=json_decode(getenv("DB_JSON"),true); echo $v["username"] ?? "";')"
    db_pass="$(DB_JSON="$db_json" php -r '$v=json_decode(getenv("DB_JSON"),true); echo $v["password"] ?? "";')"

    if [[ -n "$db_name" && -n "$db_user" ]]; then
        log "Membuat backup database sebelum migrasi..."
        MYSQL_PWD="$db_pass" mysqldump --single-transaction --quick --skip-lock-tables \
            -h "$db_host" -P "$db_port" -u "$db_user" "$db_name" \
            | gzip > "$backup_dir/siscom-${timestamp}.sql.gz"
        find "$backup_dir" -type f -name 'siscom-*.sql.gz' -mtime +14 -delete
    fi
fi

log "Menjalankan migrasi database..."
php artisan migrate --force
php artisan storage:link >/dev/null 2>&1 || true

log "Membuat cache produksi..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

php artisan queue:restart || true
php artisan schedule:list >/dev/null

if [[ -d storage && -d bootstrap/cache ]]; then
    run_as_root chgrp -R www-data storage bootstrap/cache || true
    run_as_root chmod -R ug+rwX storage bootstrap/cache || true
fi

configure_nginx

php artisan up
maintenance_enabled=0

if command -v curl >/dev/null 2>&1; then
    if curl --fail --silent --show-error --max-time 15 "${APP_URL}/up" >/dev/null; then
        log "Health check berhasil."
    else
        warn "Deploy selesai, tetapi health check publik belum merespons. Periksa DNS/HTTPS dan log Nginx."
    fi
fi

log "Deploy selesai: ${APP_URL}"
