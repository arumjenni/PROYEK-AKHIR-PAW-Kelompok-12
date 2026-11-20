# Bagian 2 — FRONTEND (di-push oleh temanmu, setelah backend)

Isi: resources/views/** (Blade), public/js/** (vanilla JS), public/images/logo.jpg

## Persiapan
```bash
git clone https://github.com/USERNAME/civicwatch-traffic.git
cd civicwatch-traffic
```
Salin isi folder `civicwatch-frontend` (folder `resources` dan `public`) ke root repo hasil clone. Strukturnya sudah sama, jadi saling menggabung ke folder yang benar. Folder `PUSH_GUIDE.md` ini tidak perlu ikut disalin.

## Tahap (push bertahap, beri jeda nyata)
**1. Layout & aset**
```bash
git add public/images public/js/app.js resources/views/layouts resources/views/partials resources/views/components
git commit -m "feat: layout utama, komponen, dan aset"
git push
```
**2. Halaman autentikasi**
```bash
git add resources/views/auth
git commit -m "feat: halaman login dan registrasi"
git push
```
**3. Antarmuka warga**
```bash
git add resources/views/warga public/js/camera.js public/js/map.js
git commit -m "feat: UI warga (beranda, lapor dengan kamera+GPS, riwayat, hadiah, profil)"
git push
```
**4. Antarmuka petugas**
```bash
git add resources/views/petugas resources/views/staff
git commit -m "feat: UI petugas (beranda, antrean, review, riwayat)"
git push
```
**5. Antarmuka admin**
```bash
git add resources/views/admin public/js/export.js
git commit -m "feat: UI admin (dashboard, pengguna, reward, ekspor)"
git push
```
Cek: `git status` bersih. Setelah ini proyek lengkap dan bisa dijalankan (`composer install`, `php artisan migrate --seed`, `php artisan serve`).
