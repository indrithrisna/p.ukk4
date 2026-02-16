# Panduan Foto Profile

## Cara Mengaktifkan Fitur Foto Profile

### 1. Update Database
Jalankan salah satu cara berikut:

**Cara A - Via Browser:**
```
http://localhost/p.ukk4/update_foto_profile.php
```

**Cara B - Via phpMyAdmin:**
Jalankan query SQL berikut:
```sql
ALTER TABLE users ADD COLUMN foto_profile VARCHAR(255) DEFAULT NULL AFTER telepon;
```

### 2. Cara Upload Foto Profile

1. Login sebagai Admin
2. Buka menu **Profile** di sidebar
3. Di bagian kiri ada card "Foto Profile"
4. Klik tombol **Choose File** dan pilih foto
5. Klik tombol **Upload Foto**
6. Foto akan langsung muncul di sidebar

### 3. Format Foto yang Didukung

- JPG / JPEG
- PNG
- GIF
- Maksimal ukuran: 2MB (bisa diatur di php.ini)

### 4. Lokasi Penyimpanan

Foto disimpan di folder:
```
uploads/profiles/
```

Format nama file:
```
profile_{user_id}_{timestamp}.{extension}
```

Contoh: `profile_1_1234567890.jpg`

### 5. Fitur yang Sudah Ditambahkan

✅ Upload foto profile di halaman profile
✅ Preview foto di sidebar (avatar bulat)
✅ Foto tersimpan di database (kolom foto_profile)
✅ Foto lama otomatis terhapus saat upload foto baru
✅ Fallback ke icon jika belum upload foto
✅ Session menyimpan foto_profile untuk akses cepat

### 6. Keamanan

- File upload dibatasi hanya image (jpg, png, gif)
- Nama file di-generate unique dengan timestamp
- Folder uploads/ harus ada permission write (777)
- Validasi file type sebelum upload

### 7. Troubleshooting

**Foto tidak muncul?**
- Pastikan folder `uploads/profiles/` sudah dibuat
- Cek permission folder (harus writable)
- Pastikan kolom `foto_profile` sudah ada di database

**Error saat upload?**
- Cek ukuran file (max 2MB)
- Cek format file (harus jpg/png/gif)
- Cek permission folder uploads

**Foto lama tidak terhapus?**
- Cek permission folder uploads
- Pastikan path foto di database benar

### 8. Cara Menghapus Foto

Saat ini belum ada tombol hapus foto. Untuk menghapus:
1. Upload foto baru (foto lama otomatis terhapus)
2. Atau set NULL di database:
```sql
UPDATE users SET foto_profile = NULL WHERE id = {user_id};
```

## Catatan

- Fitur ini bisa diterapkan untuk semua role (admin, petugas, peminjam)
- Foto profile juga bisa ditampilkan di navbar
- Bisa ditambahkan crop/resize image untuk optimasi
