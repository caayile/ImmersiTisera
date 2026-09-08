@echo off
rem Pasang sekali agar setiap git pull otomatis menjalankan migration + build.
git config core.hooksPath hooks
if %errorlevel% neq 0 (
    echo Gagal memasang hook.
    exit /b 1
)
echo.
echo Hook berhasil dipasang. Mulai sekarang setiap "git pull" otomatis:
echo   - membersihkan cache Laravel
echo   - menjalankan migration terbaru
echo   - mengisi subtitle/gambar yang belum ada
echo   - membangun asset CSS/JS
echo.
echo Untuk mengaktifkan perubahan yang sudah ter-pull SEBELUMNYA, jalankan sekali: sync-update.bat