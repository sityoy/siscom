# SIS.COM

Platform internal PT Sityoy IT Solutions untuk pengelolaan client, project, invoice, pembayaran, tiket dukungan, serta dokumen rekanan dan SPJ.

## Production

- URL: `https://siscom.sityoyitsolutions.com`
- Framework: Laravel 12
- PHP: 8.2 atau lebih baru
- Database: MySQL/MariaDB
- Frontend build: Vite

## Deploy satu perintah

Di VPS, dari folder aplikasi:

```bash
cd /var/www/siscom
bash deploy.sh
```

Skrip tersebut otomatis:

1. memastikan working tree server bersih;
2. menarik branch `master` terbaru;
3. mengatur URL produksi dan cookie HTTPS;
4. memasang dependency serta membangun aset;
5. mengaktifkan maintenance mode;
6. membuat backup database bila `mysqldump` tersedia;
7. menjalankan migrasi;
8. membangun seluruh cache Laravel;
9. me-restart queue;
10. menyiapkan Nginx/HTTPS bila belum ada; dan
11. menjalankan health check `/up`.

Variabel opsional:

```bash
APP_ROOT=/var/www/siscom \
DEPLOY_BRANCH=master \
APP_DOMAIN=siscom.sityoyitsolutions.com \
CONFIGURE_NGINX=auto \
bash deploy.sh
```

Gunakan `CONFIGURE_NGINX=0` apabila virtual host dikelola secara terpisah.

## Development

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run dev
```

Jangan menyimpan kredensial nyata di `.env.example` atau file lain yang masuk Git. Seluruh rahasia hanya boleh disimpan di `.env` server.
