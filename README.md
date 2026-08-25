# E-Warung Toko Podomoro

Aplikasi web E-Warung untuk Toko Podomoro yang dibangun menggunakan PHP prosedural dan MySQL. Aplikasi mendukung penyajian informasi barang, pengelolaan stok dan varian harga, keranjang belanja, checkout, riwayat transaksi, serta pengelolaan data oleh admin.

Repository ini merupakan bagian dari penelitian skripsi mengenai aplikasi E-Warung berbasis cloud, integrasi WhatsApp Chatbot, Multi-Layer Security, dan pengujian kinerja sistem.

## Teknologi

- PHP 8.x (prosedural)
- MySQL 8.x atau versi kompatibel
- Apache Web Server
- Bootstrap 5
- JavaScript Fetch API
- Laragon untuk pengembangan lokal

## Fitur yang Sudah Tersedia

### Pengunjung

- Melihat daftar, harga, stok, dan detail barang
- Mencari barang
- Registrasi dan login

### User

- Mengelola keranjang belanja
- Memilih varian satuan dan harga
- Checkout dan mengirim pesanan melalui WhatsApp
- Melihat profil dan riwayat transaksi

### Admin

- Melihat dashboard
- Menambah, mengubah, dan menghapus barang
- Mengelola varian harga dan stok
- Melihat pengguna dan transaksi
- Mengubah status pesanan

## Status Pengembangan

Aplikasi masih dalam tahap pengembangan. WhatsApp Chatbot melalui WhatsApp Cloud API, deployment ke VPS Rumahweb, penyempurnaan Multi-Layer Security, serta pengujian keamanan dan performa belum selesai.

## Struktur Direktori

```text
ewarungPodomoro/
├── admin/                    # Halaman administrasi
├── api/                      # Endpoint JSON aplikasi
├── assets/img/               # Gambar dan aset tampilan
├── config/                   # Konfigurasi koneksi database
├── database/                 # Struktur database tanpa data pribadi
├── functions/                # Helper, keamanan, dan rate limiting
├── user/                     # Halaman khusus user
├── index.php                 # Halaman utama
├── barang.php                # Daftar barang
├── detail.php                # Detail barang
├── login.php
├── register.php
└── logout.php
```

## Instalasi Lokal dengan Laragon

1. Clone repository ke folder `www` Laragon.

   ```bash
   git clone https://github.com/AndhikaFachrul/ewarungPodomoro.git
   ```

2. Jalankan Apache dan MySQL melalui Laragon.

3. Import file berikut melalui phpMyAdmin:

   ```text
   database/ewarung_podomoro.sql
   ```

4. Buat konfigurasi koneksi lokal pada `config/koneksi.php`:

   ```php
   <?php
   $host = 'localhost';
   $user = 'root';
   $pass = '';
   $db   = 'ewarung_podomoro';

   $conn = mysqli_connect($host, $user, $pass, $db);

   if (!$conn) {
       die('Koneksi database gagal.');
   }
   ```

5. Buka aplikasi melalui alamat Laragon, misalnya:

   ```text
   http://localhost/ewarungPodomoro/
   ```

## Membuat Akun Admin Lokal

1. Registrasikan akun melalui halaman aplikasi.
2. Buka phpMyAdmin.
3. Jalankan query berikut dengan mengganti alamat email:

   ```sql
   UPDATE users
   SET role = 'admin'
   WHERE email = 'email-admin@example.com';
   ```

Password tetap tersimpan dalam bentuk hash karena akun dibuat melalui proses registrasi aplikasi.

## Backup Database Lokal

Backup penuh dapat memuat nama, nomor WhatsApp, akun, dan transaksi. Simpan backup tersebut di komputer pribadi dan jangan commit ke repository publik.

Melalui Laragon Terminal:

```bash
mysqldump -u root -p --single-transaction --routines --triggers --events --default-character-set=utf8mb4 ewarung_podomoro > ewarung_podomoro_backup.sql
```

Jika password MySQL lokal kosong, tekan Enter ketika diminta password.

Untuk mengekspor struktur tanpa data:

```bash
mysqldump -u root -p --no-data --routines --triggers --default-character-set=utf8mb4 ewarung_podomoro > database/ewarung_podomoro.sql
```

Direktori `database/backups/` dan `database/dumps/` diabaikan oleh Git agar backup yang berisi data nyata tidak terunggah.

## Rate Limiting

Modul rate limiting mendukung:

- Fixed Window Counter sebagai algoritma pembanding
- Token Bucket sebagai algoritma utama

Endpoint utama yang dilindungi:

- `/api/login.php`
- `/api/register.php`
- `/api/get_barang.php`
- `/api/checkout.php`

## Catatan Keamanan

- Jangan menyimpan credential VPS, password database production, token WhatsApp, atau token Meta di repository.
- Gunakan prepared statement untuk query yang menerima input.
- Nonaktifkan `display_errors` pada production.
- Gunakan HTTPS sebelum mengaktifkan webhook WhatsApp.
- Lakukan pengujian keamanan hanya pada sistem yang dimiliki atau telah diizinkan.

## Lisensi

Repository ini digunakan untuk keperluan penelitian dan pengembangan skripsi.

