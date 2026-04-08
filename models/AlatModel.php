<?php
require_once __DIR__ . '/Model.php';

class AlatModel extends Model {

    public function getAll($includeDeleted = false) {
        if ($includeDeleted) {
            return $this->fetchAll("SELECT a.*, k.nama_kategori FROM alat a
                                    LEFT JOIN kategori k ON a.kategori_id = k.id
                                    WHERE a.deleted_at IS NOT NULL ORDER BY a.deleted_at DESC");
        }
        return $this->fetchAll("SELECT a.*, k.nama_kategori FROM alat a
                                LEFT JOIN kategori k ON a.kategori_id = k.id
                                WHERE a.deleted_at IS NULL ORDER BY a.id ASC");
    }

    public function getById($id) {
        return $this->fetchOne("SELECT a.*, k.nama_kategori FROM alat a
                                LEFT JOIN kategori k ON a.kategori_id = k.id
                                WHERE a.id = " . (int)$id);
    }

    public function getAvailable() {
        return $this->fetchAll("SELECT a.*, k.nama_kategori FROM alat a
                                LEFT JOIN kategori k ON a.kategori_id = k.id
                                WHERE a.deleted_at IS NULL AND a.jumlah_tersedia > 0
                                ORDER BY a.nama_alat ASC");
    }

    public function create($data) {
        $nama       = $this->escape($data['nama_alat']);
        $merk       = $this->escape($data['merk'] ?? '');
        $kat_id     = (int)$data['kategori_id'];
        $total      = (int)$data['jumlah_total'];
        $tersedia   = (int)$data['jumlah_tersedia'];
        $kondisi    = $this->escape($data['kondisi']);
        $harga      = (float)$data['harga_sewa'];
        $deskripsi  = $this->escape($data['deskripsi'] ?? '');
        return $this->query("INSERT INTO alat (nama_alat, merk, kategori_id, jumlah_total, jumlah_tersedia, kondisi, harga_sewa, deskripsi)
                             VALUES ('$nama','$merk',$kat_id,$total,$tersedia,'$kondisi',$harga,'$deskripsi')");
    }

    public function update($id, $data) {
        $id        = (int)$id;
        $nama      = $this->escape($data['nama_alat']);
        $merk      = $this->escape($data['merk'] ?? '');
        $kat_id    = (int)$data['kategori_id'];
        $total     = (int)$data['jumlah_total'];
        $tersedia  = (int)$data['jumlah_tersedia'];
        $kondisi   = $this->escape($data['kondisi']);
        $harga     = (float)$data['harga_sewa'];
        $deskripsi = $this->escape($data['deskripsi'] ?? '');
        return $this->query("UPDATE alat SET nama_alat='$nama', merk='$merk', kategori_id=$kat_id,
                             jumlah_total=$total, jumlah_tersedia=$tersedia,
                             kondisi='$kondisi', harga_sewa=$harga, deskripsi='$deskripsi'
                             WHERE id=$id");
    }

    public function softDelete($id) {
        return $this->query("UPDATE alat SET deleted_at = NOW() WHERE id = " . (int)$id);
    }

    public function restore($id) {
        return $this->query("UPDATE alat SET deleted_at = NULL WHERE id = " . (int)$id);
    }

    public function permanentDelete($id) {
        return $this->query("DELETE FROM alat WHERE id = " . (int)$id);
    }

    public function updateStok($id, $jumlah) {
        return $this->query("UPDATE alat SET jumlah_tersedia = jumlah_tersedia + " . (int)$jumlah . " WHERE id = " . (int)$id);
    }

    public function kurangiStok($id, $jumlah) {
        return $this->query("UPDATE alat SET jumlah_tersedia = jumlah_tersedia - " . (int)$jumlah . " WHERE id = " . (int)$id);
    }

    public function countTotal() {
        $row = $this->fetchOne("SELECT COUNT(*) as total FROM alat WHERE deleted_at IS NULL");
        return $row['total'];
    }

    public function getPopuler($limit = 5) {
        return $this->fetchAll("SELECT a.nama_alat, COUNT(dp.id) as jumlah_peminjaman, SUM(dp.jumlah) as total_unit
                                FROM detail_peminjaman dp
                                JOIN alat a ON dp.alat_id = a.id
                                JOIN peminjaman p ON dp.peminjaman_id = p.id
                                WHERE p.status IN ('disetujui','dipinjam','selesai')
                                GROUP BY a.id ORDER BY jumlah_peminjaman DESC LIMIT $limit");
    }
}
?>
