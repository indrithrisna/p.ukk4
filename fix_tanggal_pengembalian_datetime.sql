-- Fix tanggal_pengembalian column to include time
-- Change from DATE to DATETIME to store both date and time

ALTER TABLE `peminjaman` 
MODIFY COLUMN `tanggal_pengembalian` DATETIME NULL DEFAULT NULL;

-- Update existing records to set current time if only date exists
UPDATE `peminjaman` 
SET `tanggal_pengembalian` = CONCAT(DATE(`tanggal_pengembalian`), ' ', CURTIME())
WHERE `tanggal_pengembalian` IS NOT NULL 
  AND TIME(`tanggal_pengembalian`) = '00:00:00'
  AND `status` = 'selesai';
