# Bagian 1 — BACKEND (di-push oleh kamu, duluan)

Isi: app/, routes/, database/, config/, bootstrap/, lang/, public/index.php, composer.json, artisan, .env.example, .gitignore, README.md

## Aturan main (biar riwayat git-nya jujur)
- Push **bertahap sesuai waktu kerja kamu**, jangan sekaligus. Jangan mengubah tanggal commit (backdate) — cukup beri jeda nyata antar push (mis. 1 tahap per hari/sesi).
- Jangan commit `.env`, `vendor/`, dan foto upload (sudah di .gitignore).

## Persiapan (sekali saja)
```bash
cd civicwatch-backend
git init -b main
git remote add origin https://github.com/USERNAME/civicwatch-traffic.git
```

## Tahap (satu tahap = satu sesi push)
**1. Kerangka Laravel**
```bash
git add composer.json artisan bootstrap config public/index.php public/.htaccess storage .gitignore .env.example
git commit -m "chore: inisialisasi kerangka Laravel 12 dan konfigurasi"
git push -u origin main
```
**2. Database**
```bash
git add database
git commit -m "feat: migrasi tabel dan seeder akun awal"
git push
```
**3. Model & autentikasi**
```bash
git add app/Models app/Providers app/Http/Middleware app/Http/Controllers/Auth app/Http/Controllers/HomeController.php lang
git commit -m "feat: model, login/registrasi, middleware role"
git push
```
**4. Fitur warga**
```bash
git add app/Http/Controllers/Warga app/Policies
git commit -m "feat: controller warga (laporan, reward, profil)"
git push
```
**5. Fitur petugas & admin + routes**
```bash
git add app/Http/Controllers/Petugas app/Http/Controllers/Staff app/Http/Controllers/Admin routes
git commit -m "feat: antrean verifikasi petugas, panel admin, ekspor, routes"
git push
```
**6. Dokumentasi**
```bash
git add README.md PUSH_GUIDE.md
git commit -m "docs: README"
git push
```
Cek sisa: `git status` harus bersih.

## Lalu teman kamu
Beri dia akses repo (Settings → Collaborators). Dia `git clone`, lalu menyalin isi folder **civicwatch-frontend** ke root repo dan push bertahap (lihat PUSH_GUIDE di folder itu).

> Catatan: backend saja belum bisa dijalankan penuh (view belum ada). Setelah bagian frontend digabung, jalankan seperti di README.md.
