<?php
require_once __DIR__ . '/Model.php';

class PeminjamanModel extends Model {

    public function getAll() {
        return $this->fetchAll("SELECT p.*, u.nama FROM peminjaman p
                                JOIN users u ON p.peminjam_id = u.id
                                ORDER BY p.id ASC");
    }

    public function getById($id) {
        return $this->fetchOne("SELECT p.*, u.nama, u.email, u.telepon FROM peminjaman p
                                JOIN users u ON p.peminjam_id = u.id
                                WHERE p.id = " . (int)$id);
    }

    public function getByPeminjam($user_id) {
        return $this->fetchAll("SELECT p.* FROM peminjaman p
                                WHERE p.peminjam_id = " . (int)$user_id . "
                                ORDER BY p.id DESC");
    }

    public function getByStatus($status) {
        $status = $this->escape($status);
        return $this->fetchAll("SELECT p.*, u.nama FROM peminjaman p
                                JOIN users u ON p.peminjam_id = u.id
                                WHERE p.status = '$status'
                                ORDER BY p.tanggal_kembali ASC");
    }

    public function getDipinjam() {
        return $this->fetchAll("SELECT p.*, u.nama FROM peminjaman p
                                JOIN users u ON p.peminjam_id = u.id
                                WHERE p.status = 'dipinjam'
                                ORDER BY p.tanggal_kembali ASC");
    }

    public function getTerlambat() {
        return $this->fetchAll("SELECT p.*, u.nama,
                                DATEDIFF(CURDATE(), p.tanggal_kembali) as hari_terlambat
                                FROM peminjaman p
                                JOIN users u ON p.peminjam_id = u.id
                                WHERE p.status = 'dipinjam' AND p.tanggal_kembali < CURDATE()
                                ORDER BY p.tanggal_kembali ASC");
    }

    public function create($data) {
        $peminjam_id    = (int)$data['peminjam_id'];
        $tgl_pinjam     = $this->escape($data['tanggal_pinjam']);
        $tgl_kembali    = $this->escape($data['tanggal_kembali']);
        $total_biaya    = (float)$data['total_biaya'];
        $keterangan     = $this->escape($data['keterangan'] ?? '');
        $this->query("INSERT INTO peminjaman (peminjam_id, tanggal_pinjam, tanggal_kembali, total_biaya, keterangan, status)
                      VALUES ($peminjam_id,'$tgl_pinjam','$tgl_kembali',$total_biaya,'$keterangan','pending')");
        return mysqli_insert_id($this->conn);
    }

    public function updateStatus($id, $status, $petugas_id = null) {
        $id     = (int)$id;
        $status = $this->escape($status);
        if ($petugas_id) {
            return $this->query("UPDATE peminjaman SET status='$status', petugas_id=" . (int)$petugas_id . " WHERE id=$id");
        }
        return $this->query("UPDATE peminjaman SET status='$status' WHERE id=$id");
    }

    public function selesai($id, $denda = 0, $kondisi = 'baik', $catatan = '') {
        $id      = (int)$id;
        $denda   = (float)$denda;
        $kondisi = $this->escape($kondisi);
        $catatan = $this->escape($catatan);
        return $this->query("UPDATE peminjaman SET status='selesai', tanggal_pengembalian=NOW(),
                             denda=$denda, kondisi_pengembalian='$kondisi', catatan_pengembalian='$catatan'
                             WHERE id=$id");
    }

    public function delete($id) {
        return $this->query("DELETE FROM peminjaman WHERE id = " . (int)$id);
    }

    public function countByStatus($status) {
        $status = $this->escape($status);
        $row = $this->fetchOne("SELECT COUNT(*) as total FROM peminjaman WHERE status='$status'");
        return $row['total'];
    }

    public function countAll() {
        $row = $this->fetchOne("SELECT COUNT(*) as total FROM peminjaman");
        return $row['total'];
    }

    public function getTotalPendapatan() {
        $row = $this->fetchOne("SELECT SUM(total_biaya) as total FROM peminjaman WHERE status='selesai'");
        return $row['total'] ?? 0;
    }

    public function getTotalDenda() {
        $row = $this->fetchOne("SELECT SUM(denda) as total FROM peminjaman WHERE denda > 0");
        return $row['total'] ?? 0;
    }

    public function getPeminjamAktif($limit = 5) {
        return $this->fetchAll("SELECT u.nama, COUNT(p.id) as jumlah_peminjaman, SUM(p.total_biaya) as total_biaya
                                FROM peminjaman p
                                JOIN users u ON p.peminjam_id = u.id
                                GROUP BY u.id ORDER BY jumlah_peminjaman DESC LIMIT $limit");
    }

    // Detail peminjaman
    public function getDetail($peminjaman_id) {
        return $this->fetchAll("SELECT dp.*, a.nama_alat, a.harga_sewa FROM detail_peminjaman dp
                                JOIN alat a ON dp.alat_id = a.id
                                WHERE dp.peminjaman_id = " . (int)$peminjaman_id);
    }

    public function addDetail($peminjaman_id, $alat_id, $jumlah, $harga_satuan, $subtotal) {
        return $this->query("INSERT INTO detail_peminjaman (peminjaman_id, alat_id, jumlah, harga_satuan, subtotal)
                             VALUES (" . (int)$peminjaman_id . "," . (int)$alat_id . "," . (int)$jumlah . ",$harga_satuan,$subtotal)");
    }
}
?>
