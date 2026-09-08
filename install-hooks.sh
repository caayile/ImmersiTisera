#!/usr/bin/env bash
# Pasang sekali agar setiap git pull otomatis menjalankan migration + build.
set -euo pipefail

git config core.hooksPath hooks

echo ""
echo "Hook berhasil dipasang. Mulai sekarang setiap 'git pull' otomatis:"
echo "  - membersihkan cache Laravel"
echo "  - menjalankan migration terbaru"
echo "  - mengisi subtitle/gambar yang belum ada"
echo "  - membangun asset CSS/JS"
echo ""
echo "Untuk mengaktifkan perubahan yang sudah ter-pull SEBELUMNYA, jalankan sekali: bash sync-update.sh"