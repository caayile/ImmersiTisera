@echo off
rem Jalankan ini sekali setelah git pull untuk mengaktifkan semua perubahan.
rem Perintah:  sync-update
setlocal
echo === Mengaktifkan perubahan (.env, cache) ===
call php artisan optimize:clear
echo === Menjalankan migration terbaru ===
call php artisan migrate --force
echo === Mengisi data subtitle/gambar yang belum ada ===
call php artisan db:seed --class=Database\Seeders\DepartmentMetaBackfillSeeder --force
echo === Memasang dependency JS ===
call npm install --no-audit --no-fund
echo === Membangun asset (CSS/JS) ===
call npm run build
echo.
echo Selesai. Perubahan sudah aktif. Refresht browser dengan hard-refresh (Ctrl+F5).
endlocal