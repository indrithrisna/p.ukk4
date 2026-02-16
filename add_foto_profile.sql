-- Add foto_profile column to users table
ALTER TABLE users ADD COLUMN foto_profile VARCHAR(255) DEFAULT NULL AFTER telepon;
