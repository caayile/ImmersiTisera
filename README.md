# Imersi — TSU Industry Immersion Digital Program System

Aplikasi Laravel + React dalam **satu folder** untuk mengelola perjalanan **TSU Industry Immersion**:

**REGISTRATION → MATCHING → AGREEMENT → IMMERSION → LOGBOOK → MENTORING → OUTPUT → EVALUATION → COLLABORATION**

Kerangka program: **IDENTIFY → IMMERSION → INTERACTION → IMPACT → INTEGRATION**

## Stack

- Laravel 13 (API + Blade) + React (Vite) + Tailwind CSS
- MySQL (atau SQLite untuk development lokal)
- Session authentication + role middleware
- Eloquent, validation, storage upload, database notifications, Chart.js

## Menjalankan

Dari folder project ini (tidak perlu `cd frontend` / `cd backend`):

```bash
composer install
npm install
php artisan migrate:fresh --seed
php artisan storage:link
composer run dev
```

Buka [http://127.0.0.1:8000](http://127.0.0.1:8000)

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
