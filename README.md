# Pelaporan dan Pemantauan Keamanan Makanan MBG

Mini Project Proyek 3 - Kasus A

## Identitas

- Nama: Irfan Ardiansah Sulaeman
- NIM: 251511043
- Kelas: 2B D3
- Repository: https://github.com/irfanardiansah320-oss/Mini-Project-MBG-Proyek-3

## Deskripsi Singkat

Aplikasi web untuk membantu masyarakat melaporkan kejadian setelah mengonsumsi makanan MBG,
meninjau laporan, mencatat tindak lanjut, dan memantau perkembangannya.
Aplikasi tidak melakukan diagnosis atau penetapan penyebab; status hanya mencatat proses penanganan laporan.

## Business Rule
Business Rule :
- BR-01 : Pelapor hanya dapat mengubah laporan miliknya selama statusnya masih “Dikirim”. Setelah ditinjau, perubahan ditolak.
Alasan: data yang sedang ditinjau harus stabil agar catatan pemantau dan informasi yang diteruskan tetap sesuai.

- BR-02 : Perubahan status hanya boleh dilakukan oleh role yang berwenang. Pemantau untuk Dikirim → Sedang ditinjau → Perlu diteruskan, Koordinator untuk Perlu diteruskan → Sudah diteruskan.
Alasan: memisahkan tanggung jawab meninjau dan meneruskan, sesuai pembagian tugas di soal.

- BR-03 : Status hanya boleh bergerak satu langkah ke depan sesuai urutan, tidak boleh melompat atau mundur.
Alasan: alur proses tetap konsisten dan tidak ada tahap peninjauan yang terlewati.

## Kebutuhan Environment

- PHP 8.3 atau lebih baru
- Composer
- MySQL atau MariaDB (misalnya lewat XAMPP)
- Git

## Langkah Instalasi

```bash
git clone https://github.com/irfanardiansah320-oss/Mini-Project-MBG-Proyek-3.git
cd Mini-Project-MBG-Proyek-3/mbgReport
composer install
cp .env.example .env
php artisan key:generate
```

## Konfigurasi Database

1. Nyalakan MySQL (XAMPP), lalu buat database kosong bernama `mbg_report`.
2. Buka file `.env` dan pastikan bagian database seperti ini:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mbg_report
DB_USERNAME=root
DB_PASSWORD=
```

Sesuaikan `DB_USERNAME` dan `DB_PASSWORD` jika MySQL Anda berbeda.
Pastikan juga `SESSION_DRIVER=database`.

## Migration dan Seeding

```bash
php artisan migrate:fresh --seed
```

Perintah ini membuat semua tabel dan mengisi data demo
(akun, sekolah, distribusi, dan laporan dengan status berbeda).

## Menjalankan Aplikasi

```bash
php artisan serve
```

Buka http://127.0.0.1:8000 di browser.

## Akun Demo

Password semua akun: `password`

| Peran | Email |
|---|---|
| Pelapor | pelapor@example.com |
| Pelapor (kedua, untuk uji ownership) | pelapor2@example.com |
| Pemantau Komunitas | pemantau@example.com |
| Koordinator Komunitas | koordinator@example.com |

## Status Laporan

Dikirim -> Sedang ditinjau -> Perlu diteruskan -> Sudah diteruskan


