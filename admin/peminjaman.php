<?php
session_start();
require_once '../config/database.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header("Location: ../auth/login.php");
    exit();
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM peminjaman WHERE id = $id");
    logActivity($_SESSION['user_id'], 'Hapus Peminjaman', "Menghapus peminjaman ID: $id");
    header("Location: peminjaman.php");
    exit();
}

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
                <i class="bi bi-info-circle"></i> Persetujuan peminjaman dilakukan oleh <strong>Petugas</strong>. Admin hanya dapat melihat dan menghapus data peminjaman.
            </div>
            
            <div class="card">
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Peminjam</th>
                                <th>Alat yang Dipinjam</th>
                                <th>Tanggal Pinjam</th>
                                <th>Tanggal Kembali</th>
                                <th>Status</th>
                                <th>Total Biaya</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT p.*, u.nama FROM peminjaman p 
                                     JOIN users u ON p.peminjam_id = u.id 
                                     ORDER BY p.id ASC";
                            $result = mysqli_query($conn, $query);
                            $no = 1;
                            while ($row = mysqli_fetch_assoc($result)):
                                $status_color = [
                                    'pending' => 'warning',
                                    'disetujui' => 'info',
                                    'dipinjam' => 'primary',
                                    'selesai' => 'success',
                                    'ditolak' => 'danger'
                                ];
                                $color = $status_color[$row['status']] ?? 'secondary';
                                
                                // Get alat details
                                $detail_q = "SELECT dp.*, a.nama_alat FROM detail_peminjaman dp
                                            JOIN alat a ON dp.alat_id = a.id
                                            WHERE dp.peminjaman_id = {$row['id']}";
                                $detail_r = mysqli_query($conn, $detail_q);
                                $alat_list = [];
                                while ($d = mysqli_fetch_assoc($detail_r)) {
                                    $alat_list[] = $d['nama_alat'] . ' (' . $d['jumlah'] . 'x)';
                                }
                                $alat_display = implode(', ', $alat_list);
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo $row['nama']; ?></td>
                                <td>
                                    <?php 
                                    if (strlen($alat_display) > 50) {
                                        echo substr($alat_display, 0, 50) . '...';
                                    } else {
                                        echo $alat_display;
                                    }
                                    ?>
                                    <?php if ($row['keterangan']): ?>
                                    <br><small class="text-muted"><i class="bi bi-chat-text"></i> <?php echo substr($row['keterangan'], 0, 40); ?></small>
                                    <?php endif; ?>
                                </td>
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
                                    <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus peminjaman ini?')">
                                        <i class="bi bi-trash"></i> Hapus
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
