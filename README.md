# Kleening Revamp

Aplikasi internal untuk mengelola operasional bisnis jasa kebersihan Kleening.id. Seluruh alur — mulai dari pemesanan, penjadwalan tim, pembuktian kerja, sampai penagihan dan pelaporan — dipusatkan dalam satu platform berbasis Laravel 12 yang berjalan di atas PHP 8.3 (FPM), PostgreSQL, dan NGINX.

## Gambaran Singkat
- **Model operasional:** pelanggan memesan layanan, admin mengatur jadwal dan tim, staf di lapangan mengunggah bukti kedatangan & hasil kerja, lalu sistem menagih, memantau pembayaran, dan menyiapkan laporan kinerja.
- **Audiens utama:** admin/owner dan staf lapangan.
- **Tujuan bisnis:** meningkatkan transparansi pekerjaan, mempercepat proses penagihan, dan menyediakan data real-time untuk pengambilan keputusan.

## Arsitektur & Teknologi
| Lapisan | Detail |
| --- | --- |
| Server web | NGINX sebagai reverse proxy, meneruskan request ke PHP-FPM 8.3 |
| Backend | Laravel 12 dengan struktur monolitik modular (Controllers, Services, Events, Notifications) |
| Database | PostgreSQL sebagai sumber data tunggal (service order, invoice, laporan) |
| Frontend | Blade + Tabler UI, Alpine.js untuk interaksi ringan, TailwindCSS melalui Vite |
| Build tools | Vite untuk asset bundling, PostCSS, Tailwind config, Laravel Vite Plugin |
| Background jobs | Laravel Scheduler + Queue (perintah artisan sudah disiapkan pada `composer.json`) |

## Fitur Tersedia (Sudah Terimplementasi)

### 1. Manajemen Service Order
- Mencatat booking layanan, alamat, tenant/owner, tim yang ditugaskan, serta status pekerjaan.
- Otomatis membatalkan order dengan status `booked` yang lebih dari 7 hari (melalui scheduler `service-orders:auto-cancel-old`).
- Menyimpan dokumentasi lapangan (foto sebelum/sesudah, bukti kedatangan) untuk kontrol kualitas.

### 2. Penagihan & Pembayaran
- Membuat invoice berdasarkan service order, mengatur termin & due date, serta memantau status (`draft`, `sent`, `unpaid`, `paid`, `overdue`).
- Scheduler `invoices:mark-overdue` secara otomatis mengubah status invoice yang melewati jatuh tempo.
- Integrasi PDF (DOMPDF) untuk mencetak invoice fisik/digital.

### 3. Sistem Notifikasi
- Event-driven notification: perubahan status Service Order (`invoiced`, `done`) dan Invoice (`paid`, `overdue`) memicu notifikasi basis data.
- UI "Lonceng" di header admin untuk melihat dan menandai notifikasi yang masuk.

### 4. Pelaporan & Analitik (Advanced Reports)
Modul laporan lengkap telah tersedia sesuai spesifikasi `advanced-report.md`:
- **Profitability Report:** Analisis pendapatan, biaya, dan profit per layanan & area.
- **Staff Utilization Report:** Menghitung jam kerja efektif staf berdasarkan timestamp foto arrival dan penyelesaian kerja.
- **Invoice Aging Report:** Memonitor piutang berdasarkan durasi keterlambatan.
- **Customer Retention & Cohort:** Analisis loyalitas pelanggan antar periode waktu.

---

## Roadmap Pengembangan (Rencana)

Fitur-fitur berikut ini **belum diimplementasikan** dalam basis kode saat ini dan merupakan bagian dari rencana pengembangan masa depan. Silakan merujuk pada file dokumentasi spesifik untuk detailnya.

### 1. Modul Absensi Internal (Attendance)
> Status: **Belum Diimplementasi**. Lihat detail di `attendance-module.md`.
- Rencana fitur: Staff clock-in/out dengan GPS, geofencing per area, approval workflow oleh owner, dan handling offline.
- Saat ini, pencatatan waktu kerja masih bergantung pada timestamp foto pekerjaan di Service Order.

### 2. Customer Self-Service Portal
> Status: **Belum Diimplementasi**. Lihat detail di `future.md`.
- Rencana fitur: Web khusus pelanggan untuk registrasi, manajemen alamat, dan pemesanan mandiri.

### 3. Integrasi & Otomasi Lanjutan
> Status: **Belum Diimplementasi**. Lihat detail di `addon.md` & `future.md`.
- **WhatsApp (Taptalk):** Pengiriman invoice otomatis ke WA pelanggan.
- **Payment Gateway (Midtrans):** Pembayaran otomatis dengan Virtual Account/QRIS.
- **Loyalty Program:** Sistem poin untuk pelanggan setia.
- **Automated Re-engagement:** Kampanye otomatis untuk pelanggan inaktif.

---

## Dependensi & Paket
### Backend (Composer)
| Paket | Fungsi |
| --- | --- |
| `laravel/framework` ^12.0 | Kerangka backend utama |
| `laravel/sanctum` ^4.2 | Autentikasi API |
| `barryvdh/laravel-dompdf` ^3.1 | Render PDF invoice & laporan |
| `spatie/image` ^3.8 | Optimasi & manipulasi gambar |
| `yajra/laravel-datatables-oracle` ^12.4 | Server-side processing untuk tabel |

### Frontend (Node/Vite)
| Paket | Fungsi |
| --- | --- |
| `vite` | Build tool |
| `tailwindcss`, `@tailwindcss/forms` | Styling UI |
| `alpinejs` | Interaksi reaktif sederhana |
| `@tabler/core` | Komponen UI Dashboard |
| `apexcharts` | Visualisasi grafik laporan |

## Setup Lokal
1. **Salin konfigurasi:** `cp .env.example .env`, sesuaikan kredensial DB dan `APP_URL`.
2. **Instal dependensi:** `composer install` dan `npm install`.
3. **Migrasi Database:** `php artisan migrate --seed` (pastikan PostgreSQL berjalan).
4. **Bangun asset:** `npm run dev` (development) atau `npm run build` (production).
5. **Jalankan server:** `php artisan serve` atau via NGINX.
6. **Jalankan Queue:** `php artisan queue:listen` (Wajib untuk event/notifikasi).
7. **Jalankan Scheduler:** Pastikan cron job aktif atau jalankan `php artisan schedule:work` untuk dev.

## Referensi File Dokumentasi
Agar tidak membingungkan, harap perhatikan status file berikut:
- **`README.md` (File ini):** Source of Truth kondisi aplikasi **saat ini**.
- **`advanced-report.md`:** Dokumentasi fitur laporan (Sudah Terimplementasi).
- **`attendance-module.md`:** Spesifikasi fitur Absensi (PLAN / Belum Ada).
- **`future.md`:** Proposal fitur jangka panjang (PLAN / Belum Ada).
- **`addon.md`:** Detail teknis rencana notifikasi & scheduler tambahan (PLAN / Sebagian Terimplementasi).
