-- Database untuk Sistem Peminjaman Alat Event
CREATE DATABASE IF NOT EXISTS event_rental;
USE event_rental;

-- Tabel Users
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telepon VARCHAR(20),
    role ENUM('admin', 'petugas', 'peminjam') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Kategori Alat
CREATE TABLE kategori (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_kategori VARCHAR(50) NOT NULL,
    deskripsi TEXT
);

-- Tabel Alat Event
CREATE TABLE alat (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_alat VARCHAR(100) NOT NULL,
    merk VARCHAR(100),
    kategori_id INT,
    jumlah_total INT NOT NULL,
    jumlah_tersedia INT NOT NULL,
    kondisi ENUM('baik', 'rusak ringan', 'rusak berat') DEFAULT 'baik',
    harga_sewa DECIMAL(10,2),
    deskripsi TEXT,
    foto VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kategori_id) REFERENCES kategori(id)
);

-- Tabel Peminjaman
CREATE TABLE peminjaman (
    id INT PRIMARY KEY AUTO_INCREMENT,
    peminjam_id INT NOT NULL,
    tanggal_pinjam DATE NOT NULL,
    tanggal_kembali DATE NOT NULL,
    tanggal_pengembalian DATE,
    status ENUM('pending', 'disetujui', 'ditolak', 'dipinjam', 'selesai') DEFAULT 'pending',
    total_biaya DECIMAL(10,2),
    denda DECIMAL(10,2) DEFAULT 0,
    kondisi_pengembalian ENUM('baik', 'rusak ringan', 'rusak berat', 'hilang') DEFAULT 'baik',
    catatan_pengembalian TEXT,
    keterangan TEXT,
    petugas_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (peminjam_id) REFERENCES users(id),
    FOREIGN KEY (petugas_id) REFERENCES users(id)
);

-- Tabel Detail Peminjaman
CREATE TABLE detail_peminjaman (
    id INT PRIMARY KEY AUTO_INCREMENT,
    peminjaman_id INT NOT NULL,
    alat_id INT NOT NULL,
    jumlah INT NOT NULL,
    harga_satuan DECIMAL(10,2),
    subtotal DECIMAL(10,2),
    FOREIGN KEY (peminjaman_id) REFERENCES peminjaman(id) ON DELETE CASCADE,
    FOREIGN KEY (alat_id) REFERENCES alat(id)
);

-- Tabel Log Aktivitas
CREATE TABLE log_aktivitas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    aktivitas TEXT NOT NULL,
    keterangan TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Tabel Pengaturan Label Role
CREATE TABLE role_labels (
    id INT PRIMARY KEY AUTO_INCREMENT,
    role_key VARCHAR(20) NOT NULL UNIQUE,
    label_singular VARCHAR(50) NOT NULL,
    label_plural VARCHAR(50) NOT NULL,
    deskripsi TEXT
);

-- Tabel Pengaturan Denda
CREATE TABLE pengaturan_denda (
    id INT PRIMARY KEY AUTO_INCREMENT,
    denda_per_hari DECIMAL(10,2) NOT NULL DEFAULT 10000,
    denda_rusak_ringan DECIMAL(10,2) NOT NULL DEFAULT 50000,
    denda_rusak_berat DECIMAL(10,2) NOT NULL DEFAULT 100000,
    denda_hilang_persen INT NOT NULL DEFAULT 100,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert data default
INSERT INTO users (username, password, nama, email, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nama Anda', 'admin@event.com', 'admin'),
('petugas1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Staff Event', 'petugas@event.com', 'petugas'),
('peminjam1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Pelanggan Event', 'peminjam@event.com', 'peminjam');

-- Insert label role default
INSERT INTO role_labels (role_key, label_singular, label_plural, deskripsi) VALUES
('admin', 'Manajer', 'Manajer', 'Pengelola sistem dengan akses penuh'),
('petugas', 'Staff', 'Staff', 'Petugas yang mengelola peminjaman dan pengembalian'),
('peminjam', 'Pelanggan', 'Pelanggan', 'Pengguna yang meminjam alat event');

-- Insert pengaturan denda default
INSERT INTO pengaturan_denda (denda_per_hari, denda_rusak_ringan, denda_rusak_berat, denda_hilang_persen) 
VALUES (10000, 50000, 100000, 100);

INSERT INTO kategori (nama_kategori, deskripsi) VALUES
('Sound System', 'Peralatan audio untuk event'),
('Lighting', 'Peralatan pencahayaan'),
('Dekorasi', 'Peralatan dekorasi event'),
('Furniture', 'Meja, kursi, dan furniture lainnya');

INSERT INTO alat (nama_alat, kategori_id, jumlah_total, jumlah_tersedia, kondisi, harga_sewa, deskripsi) VALUES
('Speaker Aktif 15 inch', 1, 10, 10, 'baik', 150000, 'Speaker aktif berkualitas tinggi'),
('Microphone Wireless', 1, 20, 20, 'baik', 50000, 'Microphone wireless profesional'),
('Mixer Audio 8 Channel', 1, 5, 5, 'baik', 200000, 'Mixer audio untuk kontrol suara'),
('Amplifier 1000 Watt', 1, 8, 8, 'baik', 175000, 'Amplifier daya besar'),
('Lampu Par LED', 2, 30, 30, 'baik', 75000, 'Lampu LED warna-warni'),
('Lampu Moving Head', 2, 15, 15, 'baik', 250000, 'Lampu moving head otomatis'),
('Lampu Strobo', 2, 10, 10, 'baik', 100000, 'Lampu strobo untuk efek'),
('Smoke Machine', 2, 5, 5, 'baik', 150000, 'Mesin asap untuk efek panggung'),
('Backdrop Kain 3x4m', 3, 20, 20, 'baik', 100000, 'Backdrop kain polos berbagai warna'),
('Standing Banner', 3, 30, 30, 'baik', 50000, 'Standing banner untuk promosi'),
('Balon Gate', 3, 10, 10, 'baik', 200000, 'Gerbang balon untuk entrance'),
('Panggung Portable 2x2m', 3, 8, 8, 'baik', 300000, 'Panggung portable modular'),
('Kursi Tiffany', 4, 100, 100, 'baik', 15000, 'Kursi tiffany untuk acara formal'),
('Kursi Futura', 4, 150, 150, 'baik', 10000, 'Kursi plastik standar'),
('Meja Bulat', 4, 20, 20, 'baik', 50000, 'Meja bulat diameter 120cm'),
('Meja Kotak Panjang', 4, 25, 25, 'baik', 45000, 'Meja kotak 180x80cm'),
('Tenda Sarnafil 5x5m', 4, 10, 10, 'baik', 500000, 'Tenda sarnafil untuk outdoor'),
('Tenda Kerucut', 4, 8, 8, 'baik', 400000, 'Tenda kerucut dekoratif'),
('Karpet Merah', 3, 50, 50, 'baik', 30000, 'Karpet merah per meter'),
('Proyektor LCD', 1, 5, 5, 'baik', 300000, 'Proyektor untuk presentasi'),
('Layar Proyektor 3x2m', 1, 5, 5, 'baik', 150000, 'Layar proyektor tripod'),
('Genset 5000 Watt', 1, 3, 3, 'baik', 500000, 'Generator listrik portable'),
('Kipas Angin Berdiri', 4, 20, 20, 'baik', 25000, 'Kipas angin standing fan'),
('AC Portable', 4, 5, 5, 'baik', 350000, 'AC portable untuk ruangan'),
('Podium Mimbar', 4, 10, 10, 'baik', 75000, 'Podium kayu untuk pembicara');
