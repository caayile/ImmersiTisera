#!/usr/bin/env bash
# Jalankan ini sekali setelah git pull untuk mengaktifkan semua perubahan.
# Perintah:  ./sync-update.sh
set -euo pipefail

echo "=== Mengaktifkan perubahan (.env, cache) ==="
php artisan optimize:clear

echo "=== Menjalankan migration terbaru ==="
php artisan migrate --force

echo "=== Mengisi data subtitle/gambar yang belum ada ==="
php artisan db:seed --class=Database\Seeders\DepartmentMetaBackfillSeeder --force

echo "=== Memasang dependency JS ==="
npm install --no-audit --no-fund

echo "=== Membangun asset (CSS/JS) ==="
npm run build

echo ""
echo "Selesai. Perubahan sudah aktif. Refresh browser dengan hard-refresh (Ctrl+F5)."