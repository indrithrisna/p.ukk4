-- Tambah kolom merk ke tabel alat
ALTER TABLE alat ADD COLUMN merk VARCHAR(100) AFTER nama_alat;

-- Set default value untuk data yang sudah ada
UPDATE alat SET merk = 'Generic' WHERE merk IS NULL OR merk = '';
