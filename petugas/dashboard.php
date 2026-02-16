<?php
session_start();
require_once '../config/database.php';

if (!isLoggedIn() || !hasRole('petugas')) {
    header("Location: ../auth/login.php");
    exit();
}

// Statistik
$total_alat = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM alat"))['total'];
$total_peminjaman = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman"))['total'];
$total_peminjam = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='peminjam'"))['total'];
$pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman WHERE status='pending'"))['total'];

$page_title = "Dashboard Petugas";
include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/petugas_sidebar.php'; ?>
        <div class="col-md-10 p-4">
            <h2>Dashboard Petugas</h2>
            <hr>
            
            <?php
            // Notifikasi untuk Petugas
            
            // Cek peminjaman pending
            $query_pending = "SELECT COUNT(*) as total FROM peminjaman WHERE status = 'pending'";
            $pending_count = mysqli_fetch_assoc(mysqli_query($conn, $query_pending))['total'];
            
            if ($pending_count > 0):
            ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <h5 class="alert-heading"><i class="bi bi-clock-fill"></i> Peminjaman Menunggu Persetujuan</h5>
                <p class="mb-0">Ada <strong><?php echo $pending_count; ?> peminjaman</strong> yang menunggu persetujuan Anda. 
                <a href="peminjaman.php" class="alert-link">Lihat sekarang</a></p>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>
            
            <?php
            // Cek peminjaman yang hampir jatuh tempo
            $query_reminder = "SELECT COUNT(*) as total 
                              FROM peminjaman 
                              WHERE status = 'dipinjam'
                              AND DATEDIFF(tanggal_kembali, CURDATE()) <= 2
                              AND DATEDIFF(tanggal_kembali, CURDATE()) >= 0";
            $reminder_count = mysqli_fetch_assoc(mysqli_query($conn, $query_reminder))['total'];
            
            if ($reminder_count > 0):
            ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <h5 class="alert-heading"><i class="bi bi-exclamation-triangle-fill"></i> Peminjaman Hampir Jatuh Tempo</h5>
                <p class="mb-0">Ada <strong><?php echo $reminder_count; ?> peminjaman</strong> yang akan jatuh tempo dalam 2 hari. 
                <a href="pemantau_pengembalian.php" class="alert-link">Pantau pengembalian</a></p>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>
            
            <?php
            // Cek peminjaman yang terlambat
            $query_late = "SELECT p.*, u.nama,
                          DATEDIFF(CURDATE(), p.tanggal_kembali) as hari_terlambat,
                          (SELECT GROUP_CONCAT(a.nama_alat SEPARATOR ', ') 
                           FROM detail_peminjaman dp 
                           JOIN alat a ON dp.alat_id = a.id 
                           WHERE dp.peminjaman_id = p.id) as alat_list
                          FROM peminjaman p 
                          JOIN users u ON p.peminjam_id = u.id
                          WHERE p.status = 'dipinjam'
                          AND p.tanggal_kembali < CURDATE()
                          ORDER BY p.tanggal_kembali ASC
                          LIMIT 5";
            $result_late = mysqli_query($conn, $query_late);
            
            if (mysqli_num_rows($result_late) > 0):
            ?>
            <div class="alert alert-danger alert-dismissible fade show pulse" role="alert">
                <h5 class="alert-heading"><i class="bi bi-exclamation-circle-fill"></i> Peminjaman Terlambat!</h5>
                <hr>
                <?php while ($late = mysqli_fetch_assoc($result_late)): ?>
                <p class="mb-2">
                    <strong>Peminjam:</strong> <?php echo $late['nama']; ?><br>
                    <strong>Alat:</strong> <?php echo $late['alat_list']; ?><br>
                    <strong>Terlambat:</strong> <span class="badge bg-danger"><?php echo $late['hari_terlambat']; ?> hari</span>
                </p>
                <hr>
                <?php endwhile; ?>
                <p class="mb-0"><strong>Segera hubungi peminjam untuk pengembalian!</strong> 
                <a href="pemantau_pengembalian.php" class="alert-link">Proses pengembalian</a></p>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>
            
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h3><?php echo $total_alat; ?></h3>
                            <p class="mb-0"><i class="bi bi-box"></i> Total Alat</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h3><?php echo $total_peminjaman; ?></h3>
                            <p class="mb-0"><i class="bi bi-clipboard-check"></i> Total Peminjaman</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h3><?php echo $total_peminjam; ?></h3>
                            <p class="mb-0"><i class="bi bi-people"></i> Total Peminjam</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h3><?php echo $pending; ?></h3>
                            <p class="mb-0"><i class="bi bi-clock"></i> Pending Approval</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="alert alert-info">
                        <h5><i class="bi bi-info-circle"></i> Selamat Datang di Dashboard Petugas</h5>
                        <p class="mb-0">Gunakan menu di sebelah kiri untuk mengelola sistem peminjaman alat event.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>


