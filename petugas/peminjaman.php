<?php
session_start();
require_once '../config/database.php';

if (!isLoggedIn() || !hasRole('petugas')) {
    header("Location: ../auth/login.php");
    exit();
}

// Handle approval/rejection
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];

    if ($action == 'approve') {
        mysqli_query($conn, "UPDATE peminjaman 
            SET status='dipinjam', petugas_id={$_SESSION['user_id']} 
            WHERE id=$id AND status='pending'");

        if (mysqli_affected_rows($conn) > 0) {
            // Kurangi stok alat hanya sekali saat approve berhasil.
            $detail_query = "SELECT alat_id, jumlah FROM detail_peminjaman WHERE peminjaman_id = $id";
            $detail_result = mysqli_query($conn, $detail_query);
            while ($detail = mysqli_fetch_assoc($detail_result)) {
                $alat_id = (int)$detail['alat_id'];
                $jumlah = (int)$detail['jumlah'];
                mysqli_query($conn, "UPDATE alat SET jumlah_tersedia = jumlah_tersedia - $jumlah WHERE id = $alat_id");
            }
            logActivity($_SESSION['user_id'], 'Approve Peminjaman', "Menyetujui peminjaman ID: $id");
        }
    } elseif ($action == 'reject') {
        mysqli_query($conn, "UPDATE peminjaman 
            SET status='ditolak', petugas_id={$_SESSION['user_id']} 
            WHERE id=$id AND status='pending'");
        if (mysqli_affected_rows($conn) > 0) {
            logActivity($_SESSION['user_id'], 'Reject Peminjaman', "Menolak peminjaman ID: $id");
        }
    }
    
    header("Location: peminjaman.php");
    exit();
}

$page_title = "Kelola Peminjaman";
include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/petugas_sidebar.php'; ?>
        <div class="col-md-10 p-4">
            <h2>Kelola Peminjaman</h2>
            <hr>
            
            <div class="card">
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Peminjam</th>
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
                                     ORDER BY p.created_at DESC";
                            $result = mysqli_query($conn, $query);
                            while ($row = mysqli_fetch_assoc($result)):
                            ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo $row['nama']; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['tanggal_pinjam'])); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['tanggal_kembali'])); ?></td>
                                <td><span class="badge bg-<?php echo $row['status'] == 'pending' ? 'warning' : 'success'; ?>"><?php echo $row['status']; ?></span></td>
                                <td>Rp <?php echo number_format($row['total_biaya'], 0, ',', '.'); ?></td>
                                <td>
                                    <?php if ($row['status'] == 'pending'): ?>
                                        <a href="?action=approve&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-success">Setujui</a>
                                        <a href="?action=reject&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger">Tolak</a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
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


