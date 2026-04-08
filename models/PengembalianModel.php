<?php
require_once __DIR__ . '/Model.php';

class PengembalianModel extends Model {

    public function getDaftarDipinjam() {
        return $this->fetchAll("SELECT p.*, u.nama,
                                DATEDIFF(CURDATE(), p.tanggal_kembali) as hari_terlambat
                                FROM peminjaman p
                                JOIN users u ON p.peminjam_id = u.id
                                WHERE p.status = 'dipinjam'
                                ORDER BY p.tanggal_kembali ASC");
    }

    public function proses($id, $data, $alatModel) {
        $id      = (int)$id;
        $denda   = (float)($data['denda'] ?? 0);
        $kondisi = mysqli_real_escape_string($this->conn, $data['kondisi_pengembalian'] ?? 'baik');
        $catatan = mysqli_real_escape_string($this->conn, $data['catatan_pengembalian'] ?? '');

        // Update status peminjaman
        $this->query("UPDATE peminjaman SET status='selesai', tanggal_pengembalian=NOW(),
                      denda=$denda, kondisi_pengembalian='$kondisi', catatan_pengembalian='$catatan'
                      WHERE id=$id");

        // Kembalikan stok alat
        $details = $this->fetchAll("SELECT alat_id, jumlah FROM detail_peminjaman WHERE peminjaman_id=$id");
        foreach ($details as $d) {
            $alatModel->updateStok($d['alat_id'], $d['jumlah']);
        }

        return true;
    }

    public function hitungDenda($peminjaman_id, $pengaturan) {
        $peminjaman = $this->fetchOne("SELECT * FROM peminjaman WHERE id = " . (int)$peminjaman_id);
        if (!$peminjaman) return 0;

        $denda = 0;
        $tgl_kembali = strtotime($peminjaman['tanggal_kembali']);
        $hari_ini    = strtotime(date('Y-m-d'));

        if ($hari_ini > $tgl_kembali) {
            $hari_terlambat = ceil(($hari_ini - $tgl_kembali) / 86400);
            $denda += $hari_terlambat * $pengaturan['denda_per_hari'];
        }

        return $denda;
    }
}
?>
