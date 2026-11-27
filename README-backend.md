# CivicWatch Traffic (Laravel 12)

Platform civic-tech untuk crowdsourced traffic enforcement.
**Status:** ketiga role (**Warga**, **Petugas**, **Admin**) sudah lengkap dan saling terhubung.

## Kebutuhan
- PHP **8.2+** (ekstensi `pdo_mysql`, `mbstring`, `openssl`, `fileinfo` — aktif secara default di XAMPP/Laragon)
- **MySQL/MariaDB** (XAMPP + phpMyAdmin)
- [Composer](https://getcomposer.org/download/)
- Koneksi internet (Tailwind, font, ikon & peta dimuat dari CDN)

## Cara menjalankan

1. Nyalakan **Apache** dan **MySQL** di XAMPP, buka phpMyAdmin (http://localhost/phpmyadmin), lalu buat database baru bernama **`civicwatch`** (collation `utf8mb4_unicode_ci`).
2. Buka terminal di folder proyek, jalankan:

```bash
composer install
php artisan migrate --seed
php artisan serve
```

3. Buka **http://localhost:8000**. Tabel akan muncul di database `civicwatch` pada phpMyAdmin.

File `.env` (dengan `APP_KEY`) sudah disertakan dan disetel untuk MySQL XAMPP bawaan: host `127.0.0.1`, port `3306`, user `root`, password kosong. Jika MySQL-mu memakai password/port lain, ubah `DB_PASSWORD` / `DB_PORT` di `.env`.

> Jika `.env` hilang: `copy .env.example .env` lalu `php artisan key:generate`.

## Akun bawaan

| Role    | Username  | Password     | Halaman setelah login |
|---------|-----------|--------------|-----------------------|
| Warga   | `warga`   | `warga123`   | `/beranda` (lengkap)  |
| Petugas | `petugas` | `petugas123` | `/petugas` (lengkap)  |
| Admin   | `admin`   | `admin123`   | `/admin` (lengkap)    |

Login bisa memakai username atau email (`warga@civicwatch.test`, dst). Warga baru juga bisa daftar di `/daftar`.

## Sudah pernah menjalankan versi sebelumnya?
Timpa semua file dengan isi zip terbaru (jangan hapus `.env` milikmu jika sudah kamu ubah), lalu cukup:
```bash
php artisan migrate --seed
```
Perintah itu menambah kolom/tabel baru dan data contoh tanpa menghapus data lama. Tidak perlu `composer install` ulang (tidak ada paket baru). Jika ingin mulai bersih: `php artisan migrate:fresh --seed`.

## Reset data
```bash
php artisan migrate:fresh --seed
```

## Mencoba dari HP
Kamera langsung (`getUserMedia`) dan GPS hanya diizinkan browser di **HTTPS atau localhost**.
- Di laptop: `localhost` sudah cukup.
- Di HP lewat IP lokal (`http://192.168.x.x:8000`, jalankan `php artisan serve --host=0.0.0.0`): kamera live akan diblokir, tetapi halaman otomatis menampilkan tombol **"Buka Kamera Ponsel"** (mode cadangan). Untuk kamera live di HP, gunakan tunnel HTTPS seperti `ngrok http 8000` atau Cloudflare Tunnel.

## Struktur singkat (MVC)
```
app/Models/            User, Report, ViolationType, RewardItem, Redemption
app/Http/Controllers/  Auth/, Warga/, Petugas/, Staff/ (antrean & review, akun), Admin/ (dashboard, user, reward, export)
app/Http/Middleware/   EnsureRole  -> middleware 'role:warga'
app/Policies/          ReportPolicy (warga hanya boleh ubah/hapus laporan miliknya yg masih pending)
database/migrations/   skema tabel          database/seeders/  akun + data contoh
resources/views/       layouts/ (app, warga, staff), warga/, petugas/, staff/, admin/, auth/
public/js/             app.js (dropdown/modal), camera.js (kamera+GPS), map.js (peta), export.js
public/images/logo.jpg logo CivicWatch
public/uploads/reports foto bukti laporan (disimpan langsung di public, tanpa storage:link)
```

## Fitur warga
- **Auth**: login/logout/daftar, pembatasan akses per role
- **Beranda**: statistik, 3 laporan terbaru, peta Leaflet (titik terverifikasi + posisi GPS), sorotan hadiah
- **Laporkan (Create)**: kamera live + stempel waktu/koordinat pada foto, Geolocation API, reverse-geocode nama jalan, validasi plat, anti-duplikat 1 jam
- **Riwayat (Read/Update/Delete)**: filter status & bulan, urutan, modal bukti; laporan *pending* bisa diubah/dihapus
- **Hadiah**: tukar poin (transaksi DB, saldo & stok tidak bisa minus), tingkat pelapor, riwayat penukaran
- **Profil**: ubah data & password

## Fitur petugas (`/petugas`)
- **Beranda**: profil + shift, jumlah antrean, ringkasan keputusan (total/terverifikasi/valid/invalid), aktivitas terbaru
- **Antrean laporan** (`/antrean`): laporan *pending*, urut terlama/terbaru, tombol *Tinjau Instan*
- **Review**: foto, metadata GPS/waktu/plat, peta lokasi, reputasi pelapor, koreksi jenis pelanggaran,
  **Setujui (Valid)** → poin otomatis masuk saldo warga · **Tolak (Invalid)** → alasan wajib. Keputusan tercatat atas nama petugas
- **Riwayat verifikasi** dan **Akun**

## Fitur admin (`/admin`) — akses penuh
- **Beranda**: kartu total/pending/valid/invalid, tren harian (7/14/30 hari), jenis pelanggaran terbanyak, waktu respons verifikasi
- **Pengguna**: cari nama/NIK/badge, tab Warga & Petugas, tambah, edit, nonaktifkan/aktifkan (akun nonaktif tidak bisa masuk, riwayat tetap tersimpan)
- **Reward**: CRUD katalog (harga, stok, aktif), pengaturan poin per jenis pelanggaran (berlaku untuk laporan baru saja)
- **Ekspor**: rentang tanggal + status + format. **CSV** diunduh langsung; **PDF** membuka halaman cetak → "Simpan sebagai PDF". NIK & identitas pelapor tidak ikut diekspor. Ada riwayat ekspor + unduh ulang
- Admin juga bisa memakai Antrean & Riwayat verifikasi (menu petugas)
