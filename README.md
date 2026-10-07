# Imersi — TSU Industry Immersion Digital Program System

Aplikasi Laravel + React dalam **satu folder** untuk mengelola perjalanan **TSU Industry Immersion**:

**REGISTRATION → MATCHING → AGREEMENT → IMMERSION → LOGBOOK → MENTORING → OUTPUT → EVALUATION → COLLABORATION**

Kerangka program: **IDENTIFY → IMMERSION → INTERACTION → IMPACT → INTEGRATION**

## Stack

- Laravel 13 (API + Blade) + React (Vite) + Tailwind CSS
- SQLite (default lokal, tanpa password), PostgreSQL, MySQL, dan Neon Postgres
- Session authentication + role middleware
- Eloquent, validation, storage upload, database notifications, Chart.js

## Menjalankan

Dari folder project ini (tidak perlu `cd frontend` / `cd backend`):

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
# Windows PowerShell: New-Item database/database.sqlite -ItemType File
# macOS/Linux: touch database/database.sqlite
php artisan migrate:fresh --seed
php artisan storage:link
composer run dev
```

Buka [http://127.0.0.1:8000](http://127.0.0.1:8000)

## Database

Default lokal adalah **SQLite** (`DB_CONNECTION=sqlite`) supaya clone baru langsung jalan tanpa setup PostgreSQL.

### Error umum: `fe_sendauth: no password supplied` / connection `pgsql`

Artinya `.env` masih pakai PostgreSQL lokal tanpa `DB_PASSWORD`. Perbaiki dengan salah satu:

**A. Paling mudah — pakai SQLite**

```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

Lalu:

```bash
# pastikan file sqlite ada
php artisan config:clear
php artisan migrate:fresh --seed
```

**B. Tetap pakai PostgreSQL lokal**

1. Buat database `imersi`
2. Isi password di `.env`: `DB_PASSWORD=...` (password user `postgres` di mesin itu)
3. `php artisan config:clear && php artisan migrate:fresh --seed`

### Koneksi lain (opsional)

- MySQL: `DB_CONNECTION=mysql` + kredensial `MYSQL_*`
- Neon: `DB_CONNECTION=neon` + `NEON_DATABASE_URL` (connection string pooled; host mengandung `-pooler`)

`composer run dev` menyalakan server Laravel dan Vite sekaligus. Satu terminal sudah cukup.

## Akun demo

Kata sandi semua akun: `password`

| Role | Email | Fungsi |
|---|---|---|
| Admin / Program Manager | `admin@imersi.id` | User, department, matching, monitoring, reports |
| Mentor | `mentor@imersi.id` | Digital Business — review agreement, logbook, mentoring |
| Mentor IT | `mentor-it@imersi.id` | Unit IT |
| Mentor COE | `mentor-coe@imersi.id` | Center Of Excellence |
| Peserta (aktif) | `dosen@imersi.id` | Program ACTIVE + logbook |
| Peserta (pengajuan) | `dosen2@imersi.id` | Matching menunggu review |
| Peserta (revisi) | `raka@imersi.id` | Agreement REVISION |
| Peserta (agreed) | `sinta@imersi.id` | Program AGREED |
| Peserta (selesai) | `bima@imersi.id` | COMPLETED + kolaborasi |

## Alur utama

1. Public: Beranda → Department → Unit Bisnis → Daftar
2. Peserta: profil → pengajuan → matching → agreement → ACTIVE → 8 minggu
3. Mentor: agreement AGREED sebelum program ACTIVE
4. Admin: matching, monitoring, reports, collaboration tracking
