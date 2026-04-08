<?php
session_start();
require_once '../config/database.php';
require_once '../controllers/PeminjamanController.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header("Location: ../auth/login.php");
    exit();
}

$controller = new PeminjamanController($conn);

if (isset($_GET['delete'])) $controller->delete((int)$_GET['delete']);

$peminjaman_list = $controller->index();
$page_title = "Data Peminjaman";
include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/admin_sidebar.php'; ?>
        <div class="col-md-10 p-4">
            <h2>Data Peminjaman</h2>
            <hr>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Persetujuan peminjaman dilakukan oleh <strong>Petugas</strong>. Admin hanya dapat melihat dan menghapus data.
            </div>
            <div class="card">
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Peminjam</th>
                                <th>Alat</th>
                                <th>Tgl Pinjam</th>
                                <th>Tgl Kembali</th>
                                <th>Status</th>
                                <th>Total Biaya</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $status_color = ['pending'=>'warning','disetujui'=>'info','dipinjam'=>'primary','selesai'=>'success','ditolak'=>'danger'];
                            $no = 1;
                            foreach ($peminjaman_list as $row):
                                $color = $status_color[$row['status']] ?? 'secondary';
                                $detail = $controller->getDetail($row['id']);
                                $alat_list_str = implode(', ', array_map(fn($d) => $d['nama_alat'].' ('.$d['jumlah'].'x)', $detail));
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                <td><?php echo strlen($alat_list_str) > 50 ? substr($alat_list_str,0,50).'...' : $alat_list_str; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['tanggal_pinjam'])); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['tanggal_kembali'])); ?></td>
                                <td><span class="badge bg-<?php echo $color; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                                <td>
                                    <strong>Rp <?php echo number_format($row['total_biaya'], 0, ',', '.'); ?></strong>
                                    <?php if ($row['denda'] > 0): ?>
                                    <br><small class="text-danger">+ Denda: Rp <?php echo number_format($row['denda'], 0, ',', '.'); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-danger" onclick="if(confirm('Hapus peminjaman ini?')) location.href='?delete=<?php echo $row['id']; ?>'">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
