-- Query untuk menambah alat event lebih banyak dengan merk
-- Jalankan di phpMyAdmin atau import file ini

USE event_rental;

-- Tambah alat baru dengan merk
INSERT INTO alat (nama_alat, merk, kategori_id, jumlah_total, jumlah_tersedia, kondisi, harga_sewa, deskripsi) VALUES
('Mixer Audio 8 Channel', 'Yamaha', 1, 5, 5, 'baik', 200000, 'Mixer audio untuk kontrol suara'),
('Amplifier 1000 Watt', 'Crown', 1, 8, 8, 'baik', 175000, 'Amplifier daya besar'),
('Lampu Moving Head', 'Philips', 2, 15, 15, 'baik', 250000, 'Lampu moving head otomatis'),
('Lampu Strobo', 'Osram', 2, 10, 10, 'baik', 100000, 'Lampu strobo untuk efek'),
('Smoke Machine', 'Antari', 2, 5, 5, 'baik', 150000, 'Mesin asap untuk efek panggung'),
('Backdrop Kain 3x4m', 'Generic', 3, 20, 20, 'baik', 100000, 'Backdrop kain polos berbagai warna'),
('Standing Banner', 'Generic', 3, 30, 30, 'baik', 50000, 'Standing banner untuk promosi'),
('Balon Gate', 'Generic', 3, 10, 10, 'baik', 200000, 'Gerbang balon untuk entrance'),
('Panggung Portable 2x2m', 'Wenger', 3, 8, 8, 'baik', 300000, 'Panggung portable modular'),
('Kursi Futura', 'Olymplast', 4, 150, 150, 'baik', 10000, 'Kursi plastik standar'),
('Meja Kotak Panjang', 'Generic', 4, 25, 25, 'baik', 45000, 'Meja kotak 180x80cm'),
('Tenda Sarnafil 5x5m', 'Roder', 4, 10, 10, 'baik', 500000, 'Tenda sarnafil untuk outdoor'),
('Tenda Kerucut', 'Generic', 4, 8, 8, 'baik', 400000, 'Tenda kerucut dekoratif'),
('Karpet Merah', 'Generic', 3, 50, 50, 'baik', 30000, 'Karpet merah per meter'),
('Proyektor LCD', 'Epson', 1, 5, 5, 'baik', 300000, 'Proyektor untuk presentasi'),
('Layar Proyektor 3x2m', 'Screenview', 1, 5, 5, 'baik', 150000, 'Layar proyektor tripod'),
('Genset 5000 Watt', 'Honda', 1, 3, 3, 'baik', 500000, 'Generator listrik portable'),
('Kipas Angin Berdiri', 'Miyako', 4, 20, 20, 'baik', 25000, 'Kipas angin standing fan'),
('AC Portable', 'Sharp', 4, 5, 5, 'baik', 350000, 'AC portable untuk ruangan'),
('Podium Mimbar', 'Generic', 4, 10, 10, 'baik', 75000, 'Podium kayu untuk pembicara');

-- Selesai
SELECT 'Alat berhasil ditambahkan dengan merk!' as status;
