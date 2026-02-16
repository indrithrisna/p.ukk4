-- Update Database untuk menambahkan fitur Log Aktivitas dan Custom Role Labels
-- Jalankan query ini jika Anda sudah punya database sebelumnya

USE event_rental;

-- Tabel Log Aktivitas
CREATE TABLE IF NOT EXISTS log_aktivitas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    aktivitas TEXT NOT NULL,
    keterangan TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Tabel Pengaturan Label Role
CREATE TABLE IF NOT EXISTS role_labels (
    id INT PRIMARY KEY AUTO_INCREMENT,
    role_key VARCHAR(20) NOT NULL UNIQUE,
    label_singular VARCHAR(50) NOT NULL,
    label_plural VARCHAR(50) NOT NULL,
    deskripsi TEXT
);

-- Insert label role default (jika belum ada)
INSERT IGNORE INTO role_labels (role_key, label_singular, label_plural, deskripsi) VALUES
('admin', 'Manajer', 'Manajer', 'Pengelola sistem dengan akses penuh'),
('petugas', 'Staff', 'Staff', 'Petugas yang mengelola peminjaman dan pengembalian'),
('peminjam', 'Pelanggan', 'Pelanggan', 'Pengguna yang meminjam alat event');

-- Selesai
SELECT 'Database berhasil diupdate!' as status;
