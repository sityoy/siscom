#!/usr/bin/env bash
# Jalankan sebagai pengguna deploy SISCOM setelah migrasi kedua aplikasi.
set -Eeuo pipefail
cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.."
# DB::connection() menggunakan kredensial aktif Laravel, termasuk cache config.
grant_sql="$(php <<'PHP'
<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$db = Illuminate\Support\Facades\DB::connection();
$identity = $db->selectOne('SELECT DATABASE() AS dbname, CURRENT_USER() AS account');
if ($identity->dbname !== 'sis_com') {
    file_put_contents("php://stderr", "Periksa konfigurasi SISCOM. Database aktif harus sis_com.\n");
    exit(1);
}
try {
    $db->select('SELECT id, code, name, contact, active, until_date, modules FROM db_portalsiscom.schools LIMIT 0');
    $db->update('UPDATE db_portalsiscom.schools SET active=active,until_date=until_date,modules=modules WHERE 1=0');
    exit(0);
} catch (Illuminate\Database\QueryException $e) {
    if (!in_array((int) ($e->errorInfo[1] ?? 0), [1044,1142,1143], true)) {
        file_put_contents("php://stderr", "Periksa keberadaan db_portalsiscom.schools dan koneksi MySQL portal.\n");
        exit(1);
    }
}
if (!preg_match('/^([a-zA-Z0-9_.-]+)@([a-zA-Z0-9.%:_\/-]+)$/D', $identity->account, $parts)) {
    file_put_contents("php://stderr", "Mintalah DBA memberi akses terbatas untuk akun MySQL aplikasi SISCOM.\n");
    exit(1);
}
echo "GRANT SELECT, UPDATE (active, until_date, modules) ON db_portalsiscom.schools TO '{$parts[1]}'@'{$parts[2]}';";
PHP
)"
if [[ -n "$grant_sql" ]]; then
    printf 'Izin yang dibutuhkan aplikasi:\n%s\n' "$grant_sql"
    printf '%s\n' "$grant_sql" | sudo mysql
fi
echo 'Akses tabel pendaftaran sekolah tersedia.'
