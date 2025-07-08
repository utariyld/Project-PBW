# 🏠 Panduan Menjalankan Fitur Booking TemanKosan

## 📋 Daftar Isi
1. [Persyaratan Sistem](#persyaratan-sistem)
2. [Setup Database](#setup-database)
3. [Menjalankan Script Setup](#menjalankan-script-setup)
4. [Verifikasi Instalasi](#verifikasi-instalasi)
5. [Cara Menggunakan Booking](#cara-menggunakan-booking)
6. [Troubleshooting](#troubleshooting)
7. [Admin Management](#admin-management)

---

## 🔧 Persyaratan Sistem

### Software yang Diperlukan:
- **PHP 7.4+** dengan ekstensi:
  - PDO
  - MySQL
  - mbstring
  - JSON
- **MySQL 5.7+** atau **MariaDB 10.2+**
- **Web Server** (Apache/Nginx)

### Verifikasi Persyaratan:
```bash
php -v                    # Cek versi PHP
php -m | grep -i pdo     # Cek ekstensi PDO
mysql --version          # Cek versi MySQL
```

---

## 🗄️ Setup Database

### 1. Buat Database
```sql
CREATE DATABASE temankosan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2. Konfigurasi Database
File `config/database.php` sudah dikonfigurasi dengan:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'temankosan');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_PORT', '3306');
```

**⚠️ Penting:** Sesuaikan konfigurasi di atas dengan setting database Anda jika berbeda.

---

## 🚀 Menjalankan Script Setup

### 1. Jalankan Setup Otomatis
Buka browser dan akses:
```
http://localhost/your-project/setup_booking_system.php
```

Script ini akan:
- ✅ Memeriksa konfigurasi database
- ✅ Test koneksi database
- ✅ Membuat struktur tabel lengkap
- ✅ Insert data sample (users, kos, facilities)
- ✅ Verifikasi fungsi booking
- ✅ Test CSRF token

### 2. Output yang Diharapkan
Script akan menampilkan:
- **Step 1:** ✅ File konfigurasi database ditemukan
- **Step 2:** ✅ Koneksi database berhasil
- **Step 3:** ✅ Schema database berhasil dijalankan
- **Step 4:** Tabel yang dibuat:
  - `users` (4 records)
  - `locations` (7 records)
  - `facilities` (8 records)
  - `kos` (5 records)
  - `kos_images` (5 records)
  - `kos_facilities` (multiple records)
  - `bookings` (0 records - siap digunakan)
  - `reviews` (0 records)
  - `user_activities` (0 records)
  - `testimonials` (0 records)

---

## ✅ Verifikasi Instalasi

### 1. Akun Sample yang Tersedia
```
Admin:  admin@temankosan.com / password
User:   john@email.com / password
User:   jane@email.com / password
Owner:  owner@email.com / password
```

### 2. Test Halaman Utama
```
http://localhost/your-project/index.php
```

### 3. Test Login
```
http://localhost/your-project/login.php
```

### 4. Test Halaman Booking
```
http://localhost/your-project/kos-detail.php?id=1
```

---

## 🎯 Cara Menggunakan Booking

### 1. Flow Booking Normal

#### A. User Registration/Login
1. User daftar/login di `login.php`
2. Sistem menyimpan session user

#### B. Pilih Kos
1. User browse kos di halaman utama
2. Klik "Lihat Detail" pada kos yang diinginkan
3. Akan redirect ke `kos-detail.php?id={kos_id}`

#### C. Proses Booking
1. Di halaman detail kos, klik tombol "Booking Sekarang"
2. Akan redirect ke `booking.php?id={kos_id}`
3. User mengisi form booking:
   - Nama lengkap
   - Email
   - Nomor telepon
   - Tanggal check-in
   - Durasi sewa (1-12 bulan)
   - Metode pembayaran
   - Catatan (opsional)

#### D. Validasi Booking
Sistem melakukan validasi:
```php
// Validasi yang dilakukan:
- Nama minimal 2 karakter
- Email format valid
- Telepon format Indonesia (08xxxxxxxxxx)
- Tanggal check-in minimal besok
- Durasi 1-12 bulan
- Metode pembayaran harus dipilih
```

#### E. Pembuatan Booking
Jika validasi berhasil:
1. Generate booking code unik (format: BKxxxxxxxx)
2. Hitung total: `(harga_kos × durasi) + admin_fee`
3. Simpan ke database tabel `bookings`
4. Log aktivitas user
5. Redirect ke halaman pembayaran

#### F. Pembayaran
1. User diarahkan ke `payment.php`
2. Menampilkan detail booking dan instruksi pembayaran
3. User melakukan pembayaran sesuai metode yang dipilih

### 2. Status Booking

| Status | Deskripsi |
|--------|-----------|
| `pending` | Menunggu pembayaran |
| `paid` | Sudah dibayar, menunggu konfirmasi |
| `confirmed` | Dikonfirmasi oleh admin/owner |
| `active` | Sedang aktif (user sudah check-in) |
| `completed` | Selesai |
| `cancelled` | Dibatalkan |
| `expired` | Kedaluwarsa |

---

## 🛠️ Troubleshooting

### Error Umum dan Solusi

#### 1. "Database connection failed"
**Penyebab:** Konfigurasi database salah
**Solusi:**
```php
// Periksa config/database.php
define('DB_HOST', 'localhost');     // Host database
define('DB_NAME', 'temankosan');    // Nama database
define('DB_USER', 'root');          // Username
define('DB_PASS', '');              // Password
```

#### 2. "Table doesn't exist"
**Penyebab:** Schema belum dijalankan
**Solusi:**
```bash
# Jalankan ulang setup
http://localhost/your-project/setup_booking_system.php
```

#### 3. "Function get_kos_by_id not found"
**Penyebab:** File functions.php tidak ter-include
**Solusi:**
```php
// Pastikan di setiap file ada:
require_once 'includes/functions.php';
```

#### 4. "CSRF token mismatch"
**Penyebab:** Session bermasalah
**Solusi:**
```php
// Pastikan session_start() ada di awal file
session_start();
```

#### 5. Booking tidak tersimpan
**Penyebab:** Error di fungsi create_booking
**Debug:**
```php
// Tambahkan di booking.php setelah create_booking():
if (!$result['success']) {
    echo "Error: " . $result['message'];
    exit;
}
```

### Debugging Mode
Aktifkan error reporting untuk debug:
```php
// Tambahkan di awal file PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
```

---

## 👨‍💼 Admin Management

### 1. Akses Admin
Login sebagai admin:
```
Email: admin@temankosan.com
Password: password
```

### 2. Kelola Booking
```
http://localhost/your-project/manage-bookings.php
```

Fitur admin:
- ✅ Lihat semua booking
- ✅ Update status booking
- ✅ Update status pembayaran
- ✅ Export data booking
- ✅ Filter dan search booking

### 3. Dashboard Admin
```
http://localhost/your-project/admin/dashboard.php
```

---

## 📱 API Endpoints

Sistem juga menyediakan API untuk integrasi:

### Live Search
```
GET /api/live-search.php?q=search_term
```

---

## 🔒 Keamanan

### 1. CSRF Protection
Sistem menggunakan CSRF token untuk semua form:
```php
// Generate token
$csrf_token = generate_csrf_token();

// Verify token
verify_csrf_token($_POST['csrf_token']);
```

### 2. SQL Injection Protection
Menggunakan prepared statements:
```php
$stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ?");
$stmt->execute([$booking_id]);
```

### 3. XSS Protection
Input disanitasi:
```php
$input = sanitize_input($_POST['input']);
```

---

## 📞 Support

Jika mengalami masalah:

1. **Cek log error** di file log web server
2. **Aktifkan debug mode** sementara
3. **Pastikan semua file ada** dan readable
4. **Cek permission folder** uploads/ dan cache/

---

## 🎉 Kesimpulan

Setelah menjalankan `setup_booking_system.php` dengan sukses, fitur booking sudah siap digunakan. User dapat:

- ✅ Registrasi dan login
- ✅ Browse dan cari kos
- ✅ Melakukan booking
- ✅ Melihat riwayat booking
- ✅ Melakukan pembayaran

Admin dapat:
- ✅ Kelola semua booking
- ✅ Update status
- ✅ Monitor sistem

**Sistem booking TemanKosan siap beroperasi! 🚀**