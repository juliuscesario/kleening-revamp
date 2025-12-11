# Kleening Revamp

Aplikasi internal untuk mengelola operasional bisnis jasa kebersihan Kleening.id. Seluruh alur — mulai dari pemesanan, penjadwalan tim, pembuktian kerja, sampai penagihan dan pelaporan — dipusatkan dalam satu platform berbasis Laravel 12 yang berjalan di atas PHP 8.3 (FPM), PostgreSQL, dan NGINX.

## Gambaran Singkat
- **Model operasional:** pelanggan memesan layanan, admin mengatur jadwal dan tim, staf di lapangan mengunggah bukti kedatangan & hasil kerja, lalu sistem menagih, memantau pembayaran, dan menyiapkan laporan kinerja.
- **Audiens utama:** admin/owner, staf lapangan, serta pelanggan (melalui portal self-service yang sedang dipersiapkan).
- **Tujuan bisnis:** meningkatkan transparansi pekerjaan, mempercepat proses penagihan, dan menyediakan data real-time untuk pengambilan keputusan.

## Arsitektur & Teknologi
| Lapisan | Detail |
| --- | --- |
| Server web | NGINX sebagai reverse proxy, meneruskan request ke PHP-FPM 8.3 |
| Backend | Laravel 12 dengan struktur monolitik modular (Controllers, Services, Events, Notifications) |
| Database | PostgreSQL sebagai sumber data tunggal (service order, invoice, attendance, laporan) |
| Frontend | Blade + Tabler UI, Alpine.js untuk interaksi ringan, TailwindCSS melalui Vite |
| Build tools | Vite untuk asset bundling, PostCSS, Tailwind config, Laravel Vite Plugin |
| Background jobs | Laravel Scheduler + Queue (perintah artisan sudah disiapkan pada `composer.json`) |

## Fungsi Utama Aplikasi
### 1. Manajemen Service Order
- Mencatat booking layanan, alamat, tenant/owner, tim yang ditugaskan, serta status pekerjaan.
- Otomatis membatalkan order dengan status `booked` yang lebih dari 7 hari (melalui scheduler `service-orders:auto-cancel-old`).
- Menyimpan dokumentasi lapangan (foto sebelum/sesudah, bukti kedatangan) untuk kontrol kualitas.

### 2. Penagihan, Pembayaran, dan Kolektabilitas
- Membuat invoice berdasarkan service order, mengatur termin & due date, serta memantau status (`draft`, `sent`, `unpaid`, `paid`, `overdue`).
- Scheduler `invoices:mark-overdue` secara otomatis mengubah status invoice yang melewati jatuh tempo.
- Integrasi PDF (DOMPDF) untuk mencetak invoice dan rencana integrasi pembayaran Midtrans + pengiriman WhatsApp (lihat bagian “Rencana Integrasi”).

### 3. Sistem Notifikasi & Komunikasi
- Event-driven notification: perubahan status Service Order (`invoiced`, `done`) dan Invoice (`paid`, `overdue`) memicu notifikasi ke Customer, Owner, Co-owner, dan Admin.
- API notifikasi (bell icon di header admin) untuk membaca/menghapus notifikasi secara real-time.
- Rencana menambahkan push WhatsApp via Taptalk API dan reminder otomatis lainnya.

### 4. Pelaporan & Analitik
Didukung oleh rencana “Advanced Reports”:
- **Profitability Report:** analisis revenue, cost, dan profit tiap layanan serta area.
- **Staff Utilization Report:** menghitung durasi kerja aktual per staf berdasarkan foto `arrival` dan cap `work_proof_completed_at`.
- **Invoice Aging Report:** membagi invoice berdasarkan bucket keterlambatan.
- **Customer Retention & Cohort:** mengukur retensi dan repeat order antar cohort pelanggan.

### 5. Operasional Lapangan & Absensi
- Modul absensi internal: clock-in/out dengan pelacakan geolokasi (`lat`, `lng`, akurasi), fallback selfie + catatan bila GPS gagal.
- Owner dapat menetapkan geofence, menyetujui entri di luar radius, melihat dashboard real-time, serta mengekspor rekap periode.
- Notifikasi untuk clock-out yang terlewat dan antrean persetujuan.

### 6. Portal Pelanggan & Customer Success (Dalam Proses)
- Portal registrasi, manajemen alamat, dan pemilihan layanan mandiri.
- Loyalty program, automated re-engagement, serta dashboard poin pelanggan (berdasarkan dokumen `future.md`).

### 7. Integrasi & Automasi (Rencana)
- **WhatsApp (Taptalk):** kirim invoice berbentuk PDF dan tandai status “Sent”.
- **Payment Gateway Midtrans:** menghasilkan tombol bayar/VA dinamis lengkap dengan webhook update status invoice.
- **Expense & Commission Module:** menghitung komisi otomatis per staf dan pencatatan beban operasional agar dashboard menunjukkan P&L riil.
- **Scheduler tambahan:** reminder upload foto, kampanye re-engagement, dan fitur OTP lainnya mengikuti dokumen `addon.md` & `future.md`.

## Dependensi & Paket
### Backend (Composer)
| Paket | Fungsi |
| --- | --- |
| `laravel/framework` ^12.0 | Kerangka backend utama |
| `laravel/sanctum` ^4.2 | Autentikasi berbasis token/API untuk modul pelanggan & integrasi |
| `laravel/tinker` ^2.10 | Konsol debugging |
| `barryvdh/laravel-dompdf` ^3.1 | Render PDF invoice & laporan |
| `spatie/image` ^3.8 | Optimasi & manipulasi gambar (bukti pekerjaan, selfie fallback) |
| `yajra/laravel-datatables-oracle` ^12.4 | Server-side processing untuk tabel data besar |

**Dependensi pengembangan:** Breeze (starter auth), Sail (opsional dockerized), Pint (formatter), Pail (log viewer real-time), PHPUnit & Mockery untuk testing.

### Frontend (Node/Vite)
| Paket | Fungsi |
| --- | --- |
| `laravel-vite-plugin`, `vite`, `@tailwindcss/vite`, `postcss`, `autoprefixer` | Pipeline build asset |
| `tailwindcss`, `@tailwindcss/forms` | Utility-first styling dan UI form |
| `alpinejs` | Interaksi ringan di Blade |
| `@tabler/core` | Komponen UI dashboard |
| `axios` | HTTP client |
| `apexcharts` | Visualisasi laporan |
| `datatables.net-bs5`, `datatables.net-responsive-bs5`, `jquery` | Tabel interaktif |
| `select2`, `sweetalert2`, `toastr` | Komponen UX tambahan (select, modal alert, toast) |
| `concurrently` | Jalankan beberapa proses dev (`php artisan serve`, queue listener, Vite) lewat satu perintah |

## Kebutuhan Sistem
- PHP 8.3 (FPM) + ekstensi standar Laravel (pgsql, mbstring, bcmath, intl, gd/imagemagick, redis bila dipakai queue).
- Composer 2.x.
- PostgreSQL 13+.
- Node.js 20 LTS & npm 10+.
- NGINX atau reverse proxy ekuivalen, dengan izin tulis ke direktori `storage/` & `bootstrap/cache/`.

## Setup Lokal
1. **Salin konfigurasi:** `cp .env.example .env`, lalu set `APP_KEY`, `APP_URL`, kredensial PostgreSQL, storage driver, dan informasi mail.
2. **Instal dependensi:** `composer install` dan `npm install`.
3. **Siapkan database:** `php artisan migrate --seed` jika seed tersedia.
4. **Bangun asset:** `npm run dev` (hot reload) atau `npm run build` untuk produksi.
5. **Jalankan aplikasi:** `php artisan serve` atau gunakan NGINX -> PHP-FPM; jalankan queue listener `php artisan queue:listen --tries=1`.
6. **Scheduler:** tambahkan cron `* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1`.

## Konfigurasi Penting
- **Database & Storage:** PostgreSQL sebagai DB utama; gunakan `FILESYSTEM_DISK=public` agar bukti kerja dapat diakses melalui `storage:link`.
- **Mail & Notification:** Siapkan SMTP/Mail provider agar notifikasi invoice & reminder terkirim; untuk WhatsApp & Midtrans sediakan variabel `TAPTALK_*` dan `MIDTRANS_*`.
- **Queue & Cache:** Gunakan driver `database` atau `redis` untuk eksekusi event/notification; selalu jalankan `php artisan queue:listen`.
- **Security:** Gunakan HTTPS di NGINX, aktifkan rate limiting pada API attendance & portal pelanggan, dan jaga kredensial di `.env`.

## Operasional & Kualitas
- **Testing:** `php artisan test` (menggunakan PHPUnit 11); tambahkan test feature/unit untuk modul baru.
- **Linting:** `./vendor/bin/pint` untuk format PHP; gunakan `npm run lint` (jika ditambahkan) untuk JS/TS.
- **Backup:** lakukan dump Postgres berkala dan sinkronisasi storage (foto bukti kerja, PDF invoice).
- **Monitoring:** gunakan `laravel/pail` untuk log real-time saat proses dev; di produksi gunakan stack logging bawaan Laravel.

## Dokumentasi Pendukung
- `addon.md` – detail scheduler & notifikasi V4.
- `advanced-report.md` – blueprint laporan Profitability, Staff Utilization, Invoice Aging, Cohort.
- `attendance-module.md` – requirement lengkap modul absensi.
- `future.md` – proposal fitur strategis (loyalty, re-engagement, komisi, expense, foto reminder).

Seluruh file ini bersama README bertindak sebagai *single source of truth* mengenai ruang lingkup fitur, paket yang dipakai, dan rencana pengembangan Kleening Revamp.

## Lisensi
Basis kode mengikuti lisensi MIT dari Laravel kecuali disebutkan lain dalam repositori ini.
