<?php
session_start();
require_once '../config/database.php';

if (!isLoggedIn() || !hasRole('peminjam')) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$total_peminjaman = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman WHERE peminjam_id=$user_id"))['total'];
$pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman WHERE peminjam_id=$user_id AND status='pending'"))['total'];
$dipinjam = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman WHERE peminjam_id=$user_id AND status='dipinjam'"))['total'];

$page_title = "Dashboard Peminjam";
include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/peminjam_sidebar.php'; ?>
        <div class="col-md-10 p-4">
            <h2>Dashboard Peminjam</h2>
            <hr>
            
            <?php
            // Notifikasi Pengingat
            $user_id = $_SESSION['user_id'];
            
            // Cek peminjaman yang hampir jatuh tempo
            $query_reminder = "SELECT p.*, 
                              DATEDIFF(p.tanggal_kembali, CURDATE()) as hari_tersisa,
                              (SELECT GROUP_CONCAT(a.nama_alat SEPARATOR ', ') 
                               FROM detail_peminjaman dp 
                               JOIN alat a ON dp.alat_id = a.id 
                               WHERE dp.peminjaman_id = p.id) as alat_list
                              FROM peminjaman p 
                              WHERE p.peminjam_id = $user_id 
                              AND p.status = 'dipinjam'
                              AND DATEDIFF(p.tanggal_kembali, CURDATE()) <= 2
                              AND DATEDIFF(p.tanggal_kembali, CURDATE()) >= 0
                              ORDER BY p.tanggal_kembali ASC";
            $result_reminder = mysqli_query($conn, $query_reminder);
            
            if (mysqli_num_rows($result_reminder) > 0):
            ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <h5 class="alert-heading"><i class="bi bi-exclamation-triangle-fill"></i> Pengingat Pengembalian!</h5>
                <hr>
                <?php while ($reminder = mysqli_fetch_assoc($result_reminder)): ?>
                <p class="mb-2">
                    <strong>Alat:</strong> <?php echo $reminder['alat_list']; ?><br>
                    <strong>Harus dikembalikan:</strong> <?php echo date('d/m/Y', strtotime($reminder['tanggal_kembali'])); ?> 
                    <span class="badge bg-warning text-dark"><?php echo $reminder['hari_tersisa']; ?> hari lagi</span>
                </p>
                <?php endwhile; ?>
                <hr>
                <p class="mb-0"><small>Segera kembalikan alat untuk menghindari denda keterlambatan!</small></p>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>
            
            <?php
            // Cek peminjaman yang terlambat
            $query_late = "SELECT p.*,
                          DATEDIFF(CURDATE(), p.tanggal_kembali) as hari_terlambat,
                          (SELECT GROUP_CONCAT(a.nama_alat SEPARATOR ', ') 
                           FROM detail_peminjaman dp 
                           JOIN alat a ON dp.alat_id = a.id 
                           WHERE dp.peminjaman_id = p.id) as alat_list
                          FROM peminjaman p 
                          WHERE p.peminjam_id = $user_id 
                          AND p.status = 'dipinjam'
                          AND p.tanggal_kembali < CURDATE()
                          ORDER BY p.tanggal_kembali ASC";
            $result_late = mysqli_query($conn, $query_late);
            
            if (mysqli_num_rows($result_late) > 0):
            ?>
            <div class="alert alert-danger alert-dismissible fade show pulse" role="alert">
                <h5 class="alert-heading"><i class="bi bi-exclamation-circle-fill"></i> Peminjaman Terlambat!</h5>
                <hr>
                <?php while ($late = mysqli_fetch_assoc($result_late)): ?>
                <p class="mb-2">
                    <strong>Alat:</strong> <?php echo $late['alat_list']; ?><br>
                    <strong>Seharusnya dikembalikan:</strong> <?php echo date('d/m/Y', strtotime($late['tanggal_kembali'])); ?> 
                    <span class="badge bg-danger">Terlambat <?php echo $late['hari_terlambat']; ?> hari</span>
                </p>
                <?php endwhile; ?>
                <hr>
                <p class="mb-0"><strong>SEGERA kembalikan alat! Anda akan dikenakan denda keterlambatan.</strong></p>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h3><?php echo $total_peminjaman; ?></h3>
                            <p class="mb-0">Total Peminjaman</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h3><?php echo $pending; ?></h3>
                            <p class="mb-0">Menunggu Persetujuan</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h3><?php echo $dipinjam; ?></h3>
                            <p class="mb-0">Sedang Dipinjam</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Peminjaman Aktif</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tanggal Pinjam</th>
                                <th>Tanggal Kembali</th>
                                <th>Status</th>
                                <th>Total Biaya</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT * FROM peminjaman 
                                     WHERE peminjam_id = $user_id AND status IN ('pending', 'disetujui', 'dipinjam')
                                     ORDER BY created_at DESC";
                            $result = mysqli_query($conn, $query);
                            while ($row = mysqli_fetch_assoc($result)):
                            ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['tanggal_pinjam'])); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['tanggal_kembali'])); ?></td>
                                <td><span class="badge bg-<?php echo $row['status'] == 'pending' ? 'warning' : 'info'; ?>"><?php echo $row['status']; ?></span></td>
                                <td>Rp <?php echo number_format($row['total_biaya'], 0, ',', '.'); ?></td>
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


