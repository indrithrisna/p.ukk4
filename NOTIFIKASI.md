# 🔔 Sistem Notifikasi Pengingat

## Fitur Notifikasi yang Ditambahkan

### 📱 Notification Bell (di Navbar)

**Lokasi:** Header (Navbar) - Tersedia untuk semua role

**Fitur:**
- Badge merah menampilkan jumlah notifikasi
- Dropdown menu dengan daftar notifikasi
- Icon berbeda untuk setiap jenis notifikasi
- Auto-refresh saat page reload
- Animasi ring pada bell icon

### 👤 Untuk Peminjam

#### 1. Notifikasi di Bell (Navbar)
- ⚠️ **Pengingat Pengembalian** (H-2)
  - Muncul 2 hari sebelum tanggal kembali
  - Menampilkan nama alat dan tanggal kembali
  - Badge kuning (warning)

- 🚨 **Terlambat!**
  - Muncul jika sudah melewati tanggal kembali
  - Menampilkan jumlah hari terlambat
  - Badge merah (danger)
  - Status: Urgent

#### 2. Alert di Dashboard
- **Alert Kuning (Warning)** - Hampir jatuh tempo
  - Muncul jika ada peminjaman H-2
  - Menampilkan daftar alat yang harus dikembalikan
  - Countdown hari tersisa
  - Dapat di-dismiss

- **Alert Merah (Danger)** - Terlambat
  - Muncul jika sudah terlambat
  - Animasi pulse untuk menarik perhatian
  - Menampilkan jumlah hari terlambat
  - Peringatan denda
  - Dapat di-dismiss

### 👨‍💼 Untuk Petugas

#### 1. Notifikasi di Bell (Navbar)
- ⚠️ **Hampir Jatuh Tempo**
  - Menampilkan nama peminjam dan alat
  - Countdown hari tersisa
  - Badge kuning (warning)

- 🚨 **Terlambat!**
  - Menampilkan nama peminjam dan alat
  - Jumlah hari terlambat
  - Badge merah (danger)
  - Status: Urgent

- ℹ️ **Peminjaman Pending**
  - Jumlah peminjaman yang menunggu persetujuan
  - Badge biru (info)
  - Status: Baru

#### 2. Alert di Dashboard
- **Alert Biru (Info)** - Peminjaman Pending
  - Jumlah peminjaman pending
  - Link ke halaman peminjaman
  - Dapat di-dismiss

- **Alert Kuning (Warning)** - Hampir Jatuh Tempo
  - Jumlah peminjaman yang akan jatuh tempo
  - Link ke pemantau pengembalian
  - Dapat di-dismiss

- **Alert Merah (Danger)** - Terlambat
  - Daftar peminjaman yang terlambat (max 5)
  - Detail peminjam, alat, dan hari terlambat
  - Animasi pulse
  - Link ke pemantau pengembalian
  - Dapat di-dismiss

### 👨‍💼 Untuk Admin

#### 1. Notifikasi di Bell (Navbar)
Sama seperti Petugas:
- Hampir jatuh tempo
- Terlambat
- Peminjaman pending

## 🎨 Desain & Animasi

### Visual Elements
- **Icon:** Bootstrap Icons untuk setiap jenis notifikasi
- **Badge:** Rounded pill dengan warna sesuai tingkat urgency
- **Dropdown:** Width 350px dengan max-height 400px (scrollable)
- **Alert:** Border kiri berwarna sesuai jenis

### Animasi
1. **Bell Ring Animation**
   - Bergetar setiap 5 detik jika ada notifikasi
   - Trigger saat hover

2. **Slide In Animation**
   - Alert muncul dari kanan dengan fade
   - Duration: 0.5s

3. **Pulse Animation**
   - Untuk alert terlambat (urgent)
   - Menarik perhatian user

4. **Hover Effect**
   - Dropdown item bergeser ke kanan saat hover
   - Smooth transition

## 🔧 Cara Kerja

### Query Database
```sql
-- Cek peminjaman hampir jatuh tempo (H-2)
SELECT * FROM peminjaman 
WHERE status = 'dipinjam'
AND DATEDIFF(tanggal_kembali, CURDATE()) <= 2
AND DATEDIFF(tanggal_kembali, CURDATE()) >= 0

-- Cek peminjaman terlambat
SELECT * FROM peminjaman 
WHERE status = 'dipinjam'
AND tanggal_kembali < CURDATE()
```

### Logika Notifikasi
1. **H-2 (2 hari sebelum):** Warning notification
2. **H-1 (1 hari sebelum):** Warning notification
3. **H-0 (hari ini):** Warning notification
4. **H+1 (terlambat 1 hari):** Danger notification
5. **H+2 dst:** Danger notification dengan jumlah hari

### Badge Count
- Menghitung total notifikasi aktif
- Update setiap page reload
- Menampilkan angka di badge merah

## 📊 Prioritas Notifikasi

### Tingkat Urgency
1. 🚨 **Urgent (Danger)** - Terlambat
   - Warna: Merah
   - Animasi: Pulse
   - Action: Segera kembalikan

2. ⚠️ **Warning** - Hampir jatuh tempo
   - Warna: Kuning
   - Animasi: Slide in
   - Action: Persiapkan pengembalian

3. ℹ️ **Info** - Peminjaman pending
   - Warna: Biru
   - Animasi: Slide in
   - Action: Review dan approve

## 🎯 Best Practices

### Untuk Peminjam
1. Cek notifikasi setiap hari
2. Perhatikan alert di dashboard
3. Kembalikan alat sebelum jatuh tempo
4. Hubungi petugas jika ada kendala

### Untuk Petugas
1. Monitor notifikasi secara berkala
2. Prioritaskan peminjaman terlambat
3. Hubungi peminjam yang terlambat
4. Proses peminjaman pending dengan cepat

### Untuk Admin
1. Monitor statistik di dashboard
2. Review laporan secara berkala
3. Pastikan petugas menindaklanjuti notifikasi

## 🔮 Future Enhancements (Opsional)

1. **Email Notification**
   - Kirim email otomatis H-2
   - Email reminder untuk terlambat

2. **SMS Notification**
   - SMS reminder H-1
   - SMS urgent untuk terlambat

3. **Push Notification**
   - Browser push notification
   - Mobile app notification

4. **Real-time Updates**
   - WebSocket untuk update real-time
   - Auto-refresh tanpa reload

5. **Notification History**
   - Simpan riwayat notifikasi
   - Mark as read functionality

6. **Sound Alert**
   - Suara notifikasi untuk urgent
   - Customizable sound

7. **Notification Settings**
   - User bisa atur preferensi notifikasi
   - Enable/disable per jenis notifikasi

## 📝 Notes

- Notifikasi update setiap page reload
- Alert dapat di-dismiss oleh user
- Badge count menampilkan total notifikasi aktif
- Dropdown scrollable untuk banyak notifikasi
- Responsive untuk mobile device

## 🎉 Selesai!

Sistem notifikasi sudah aktif dan siap digunakan!
