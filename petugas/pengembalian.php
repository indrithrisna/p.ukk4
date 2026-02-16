<?php
session_start();
require_once '../config/database.php';

if (!isLoggedIn() || !hasRole('petugas')) {
    header("Location: ../auth/login.php");
    exit();
}

if (isset($_GET['selesai'])) {
    $id = (int)$_GET['selesai'];
    
    // Kembalikan stok alat
    $detail_query = "SELECT alat_id, jumlah FROM detail_peminjaman WHERE peminjaman_id = $id";
    $detail_result = mysqli_query($conn, $detail_query);
    while ($detail = mysqli_fetch_assoc($detail_result)) {
        $alat_id = $detail['alat_id'];
        $jumlah = $detail['jumlah'];
        mysqli_query($conn, "UPDATE alat SET jumlah_tersedia = jumlah_tersedia + $jumlah WHERE id = $alat_id");
    }
    
    mysqli_query($conn, "UPDATE peminjaman SET status='selesai', tanggal_pengembalian=NOW() WHERE id=$id");
    logActivity($_SESSION['user_id'], 'Pengembalian Alat', "Menyelesaikan peminjaman ID: $id dan mengembalikan stok alat");
    header("Location: pengembalian.php");
    exit();
}

$page_title = "Pengembalian";
include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/petugas_sidebar.php'; ?>
        <div class="col-md-10 p-4">
            <h2>Pengembalian Alat</h2>
            <hr>
            
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Daftar peminjaman yang sedang berlangsung dan perlu dikembalikan.
            </div>
            
            <div class="card">
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Peminjam</th>
                                <th>Tanggal Pinjam</th>
                                <th>Tanggal Kembali</th>
                                <th>Total Biaya</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT p.*, u.nama FROM peminjaman p 
                                     JOIN users u ON p.peminjam_id = u.id 
                                     WHERE p.status = 'dipinjam'
                                     ORDER BY p.tanggal_kembali ASC";
                            $result = mysqli_query($conn, $query);
                            
                            if (mysqli_num_rows($result) == 0) {
                                echo '<tr><td colspan="7" class="text-center">Tidak ada peminjaman yang sedang berlangsung</td></tr>';
                            }
                            
                            while ($row = mysqli_fetch_assoc($result)):
                                // Cek apakah terlambat
                                $terlambat = strtotime($row['tanggal_kembali']) < strtotime(date('Y-m-d'));
                            ?>
                            <tr class="<?php echo $terlambat ? 'table-warning' : ''; ?>">
                                <td><?php echo $row['id']; ?></td>
                                <td>
                                    <?php echo $row['nama']; ?>
                                    <?php if ($terlambat): ?>
                                        <br><small class="text-danger"><i class="bi bi-exclamation-triangle"></i> Terlambat</small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($row['tanggal_pinjam'])); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['tanggal_kembali'])); ?></td>
                                <td>Rp <?php echo number_format($row['total_biaya'], 0, ',', '.'); ?></td>
                                <td><span class="badge bg-info"><?php echo $row['status']; ?></span></td>
                                <td>
                                    <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#detailModal<?php echo $row['id']; ?>">
                                        <i class="bi bi-eye"></i> Detail
                                    </button>
                                    <a href="?selesai=<?php echo $row['id']; ?>" class="btn btn-sm btn-success" onclick="return confirm('Konfirmasi pengembalian alat?')">
                                        <i class="bi bi-check-circle"></i> Selesai
                                    </a>
                                </td>
                            </tr>
                            
                            <!-- Modal Detail -->
                            <div class="modal fade" id="detailModal<?php echo $row['id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Detail Peminjaman #<?php echo $row['id']; ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <h6>Informasi Peminjam</h6>
                                            <table class="table table-sm">
                                                <tr>
                                                    <td><strong>Nama</strong></td>
                                                    <td><?php echo $row['nama']; ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Tanggal Pinjam</strong></td>
                                                    <td><?php echo date('d/m/Y', strtotime($row['tanggal_pinjam'])); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Tanggal Kembali</strong></td>
                                                    <td><?php echo date('d/m/Y', strtotime($row['tanggal_kembali'])); ?></td>
                                                </tr>
                                            </table>
                                            
                                            <h6>Alat yang Dipinjam</h6>
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Alat</th>
                                                        <th>Jumlah</th>
                                                        <th>Harga</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $detail_query = "SELECT dp.*, a.nama_alat FROM detail_peminjaman dp
                                                                    JOIN alat a ON dp.alat_id = a.id
                                                                    WHERE dp.peminjaman_id = {$row['id']}";
                                                    $detail_result = mysqli_query($conn, $detail_query);
                                                    while ($detail = mysqli_fetch_assoc($detail_result)):
                                                    ?>
                                                    <tr>
                                                        <td><?php echo $detail['nama_alat']; ?></td>
                                                        <td><?php echo $detail['jumlah']; ?></td>
                                                        <td>Rp <?php echo number_format($detail['subtotal'], 0, ',', '.'); ?></td>
                                                    </tr>
                                                    <?php endwhile; ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <th colspan="2">Total</th>
                                                        <th>Rp <?php echo number_format($row['total_biaya'], 0, ',', '.'); ?></th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>


