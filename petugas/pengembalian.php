<?php
session_start();
require_once '../config/database.php';
require_once '../controllers/PengembalianController.php';
require_once '../models/PeminjamanModel.php';

if (!isLoggedIn() || !hasRole('petugas')) {
    header("Location: ../auth/login.php");
    exit();
}

$controller     = new PengembalianController($conn);
$peminjamanModel = new PeminjamanModel($conn);

if (isset($_GET['selesai'])) $controller->selesai((int)$_GET['selesai'], 'petugas');

$pengembalian_list = $controller->index();
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
                                <th>Tgl Pinjam</th>
                                <th>Tgl Kembali</th>
                                <th>Total Biaya</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pengembalian_list)): ?>
                            <tr><td colspan="7" class="text-center">Tidak ada peminjaman yang sedang berlangsung</td></tr>
                            <?php endif; ?>
                            <?php foreach ($pengembalian_list as $row):
                                $terlambat = strtotime($row['tanggal_kembali']) < strtotime(date('Y-m-d'));
                            ?>
                            <tr class="<?php echo $terlambat ? 'table-warning' : ''; ?>">
                                <td><?php echo $row['id']; ?></td>
                                <td>
                                    <?php echo htmlspecialchars($row['nama']); ?>
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
                                    <button class="btn btn-sm btn-success" onclick="if(confirm('Konfirmasi pengembalian?')) location.href='?selesai=<?php echo $row['id']; ?>'">
                                        <i class="bi bi-check-circle"></i> Selesai
                                    </button>
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
                                            <table class="table table-sm">
                                                <tr><td><strong>Nama</strong></td><td><?php echo htmlspecialchars($row['nama']); ?></td></tr>
                                                <tr><td><strong>Tgl Pinjam</strong></td><td><?php echo date('d/m/Y', strtotime($row['tanggal_pinjam'])); ?></td></tr>
                                                <tr><td><strong>Tgl Kembali</strong></td><td><?php echo date('d/m/Y', strtotime($row['tanggal_kembali'])); ?></td></tr>
                                            </table>
                                            <h6>Alat yang Dipinjam</h6>
                                            <table class="table table-sm">
                                                <thead><tr><th>Alat</th><th>Jumlah</th><th>Harga</th></tr></thead>
                                                <tbody>
                                                    <?php foreach ($peminjamanModel->getDetail($row['id']) as $d): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($d['nama_alat']); ?></td>
                                                        <td><?php echo $d['jumlah']; ?></td>
                                                        <td>Rp <?php echo number_format($d['subtotal'], 0, ',', '.'); ?></td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                                <tfoot><tr><th colspan="2">Total</th><th>Rp <?php echo number_format($row['total_biaya'], 0, ',', '.'); ?></th></tr></tfoot>
                                            </table>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
