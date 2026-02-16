# Update Merk/Brand Field - Completed

## Summary
Added merk/brand field to equipment (alat) table and updated all relevant pages to display and manage this information.

## Files Updated

### 1. Database Schema
- `database.sql` - Added merk column to alat table definition
- `update_merk.sql` - SQL script to add merk column to existing database
- `tambah_alat.sql` - Updated sample data with brand names

### 2. Admin Pages
- `admin/alat.php` - Added merk field to:
  - Table display (new column)
  - Add/Edit form (new input field)
  - Edit function (includes merk parameter)
  - SQL queries (INSERT and UPDATE)

- `admin/peminjaman.php` - Added merk column to detail modal showing borrowed equipment

### 3. Petugas Pages
- `petugas/alat.php` - Added merk field to:
  - Table display (new column)
  - Add/Edit form (new input field)
  - Edit function (includes merk parameter)
  - SQL queries (INSERT and UPDATE)

### 4. Peminjam Pages
- `peminjam/alat.php` - Added merk column to equipment list table

## Database Migration

To add the merk column to your existing database, run:

```sql
-- Run update_merk.sql
USE event_rental;

ALTER TABLE alat 
ADD COLUMN merk VARCHAR(100) AFTER nama_alat;

UPDATE alat SET merk = 'Generic' WHERE merk IS NULL OR merk = '';
```

## Features

1. **Display**: Merk is shown as a badge in all equipment lists
2. **Form Input**: Optional text field with placeholder examples (JBL, Yamaha, Sony)
3. **Edit Support**: Merk field is included in all edit operations
4. **Detail View**: Merk is shown in peminjaman detail modal
5. **Default Value**: Existing equipment without merk will show '-' or can be set to 'Generic'

## Testing Checklist

- [x] Admin can add equipment with merk
- [x] Admin can edit equipment merk
- [x] Petugas can add equipment with merk
- [x] Petugas can edit equipment merk
- [x] Peminjam can see merk in equipment list
- [x] Merk displays in peminjaman detail modal
- [x] Database migration script ready

## Notes

- Merk field is optional (not required)
- Displays as badge with secondary color
- Shows '-' if no merk is set
- All CRUD operations include merk field
