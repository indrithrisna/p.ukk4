# Sistem Peminjaman Alat Event

Website peminjaman alat event dengan sistem CRUD lengkap untuk 3 role: Admin, Petugas, dan Peminjam.

## Fitur

### Admin
- Dashboard dengan statistik
- CRUD Alat Event (Create, Read, Update, Delete)
- CRUD Kategori Alat
- CRUD User (kelola semua user)
- Kelola Peminjaman
- **Custom Label Role** - Ubah nama tampilan role sesuai kebutuhan (Admin → Manajer, Petugas → Operator, dll)
- **Log Aktivitas** - Monitor semua aktivitas user dengan filter
- Laporan

### Petugas
- Dashboard
- Lihat daftar alat
- Approve/Reject peminjaman
- Kelola pengembalian alat
- Update status peminjaman

### Peminjam
- Dashboard
- Lihat daftar alat tersedia
- Buat peminjaman baru
- Lihat status peminjaman
- Batalkan peminjaman (jika masih pending)
- Riwayat peminjaman

## Teknologi
- PHP Native
- MySQL
- Bootstrap 5
- jQuery

## Instalasi

1. Clone atau download project ini

2. Import database:
   - Buka phpMyAdmin
   - Buat database baru bernama `event_rental`
   - Import file `database.sql`
   - Database akan otomatis membuat tabel dan data default

3. Konfigurasi database di `config/database.php` (jika perlu):
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'event_rental');
   ```

4. Jalankan dengan web server (XAMPP/WAMP/LAMP):
   - Letakkan folder project di htdocs (XAMPP) atau www (WAMP)
   - Pastikan Apache dan MySQL sudah running
   - Akses via browser: `http://localhost/nama-folder-project/`
   - Contoh: `http://localhost/event-rental/`

5. Login dengan akun default (lihat di bawah)

## Default Login

### Admin
- Username: `admin`
- Password: `12345`

### Petugas
- Username: `petugas1`
- Password: `12345`

### Peminjam
- Username: `peminjam1`
- Password: `12345`

## Struktur Database

- `users` - Data pengguna (admin, petugas, peminjam)
- `kategori` - Kategori alat event
- `alat` - Data alat event
- `peminjaman` - Data peminjaman
- `detail_peminjaman` - Detail alat yang dipinjam
- `log_aktivitas` - Log semua aktivitas user
- `role_labels` - Custom label untuk setiap role

## Alur Peminjaman

1. Peminjam membuat peminjaman baru (status: pending)
2. Petugas mereview dan approve/reject peminjaman
3. Jika disetujui, petugas tandai sebagai "dipinjam" saat alat diambil
4. Saat alat dikembalikan, petugas tandai sebagai "selesai"

## Catatan
- Password default menggunakan bcrypt hash
- Semua input sudah dilindungi dari SQL Injection
- Session management untuk autentikasi
- Role-based access control
- Registrasi user baru hanya bisa dilakukan oleh admin
- Semua aktivitas penting tercatat di log aktivitas
- Label role dapat disesuaikan dengan kebutuhan organisasi

## Troubleshooting

### Error "Not Found" setelah login
1. Pastikan folder project sudah benar di htdocs/www
2. Cek URL di browser, harus: `http://localhost/nama-folder/`
3. Pastikan Apache sudah running
4. Coba akses `http://localhost/nama-folder/test_path.php` untuk cek konfigurasi

### Error Database Connection
1. Pastikan MySQL sudah running
2. Cek username/password di `config/database.php`
3. Pastikan database `event_rental` sudah dibuat dan diimport

### Error 404 pada halaman lain
1. Pastikan semua file ada di folder yang benar
2. Clear browser cache
3. Restart Apache

### Test Koneksi
Akses `http://localhost/nama-folder/test_path.php` untuk mengecek:
- Path configuration
- Database connection
- Link testing
