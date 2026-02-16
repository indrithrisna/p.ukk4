<?php
session_start();
require_once '../config/database.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header("Location: ../auth/login.php");
    exit();
}

// Statistik
$total_alat = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM alat"))['total'];
$total_peminjaman = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman"))['total'];
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='peminjam'"))['total'];
$pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman WHERE status='pending'"))['total'];

$page_title = "Dashboard Admin";
include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/admin_sidebar.php'; ?>
        
        <div class="col-md-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">Dashboard Admin</h2>
                    <p class="text-muted mb-0">Selamat datang, <?php echo $_SESSION['nama']; ?>!</p>
                </div>
                <div class="text-end">
                    <small class="text-muted">
                        <i class="bi bi-calendar3"></i> <?php echo date('d F Y'); ?>
                    </small>
                </div>
            </div>
            
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card stats-card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="mb-0"><?php echo $total_alat; ?></h3>
                                    <p class="mb-0"><i class="bi bi-box-seam"></i> Total Alat</p>
                                </div>
                                <div>
                                    <i class="bi bi-box-seam" style="font-size: 3rem; opacity: 0.3;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="mb-0"><?php echo $total_peminjaman; ?></h3>
                                    <p class="mb-0"><i class="bi bi-clipboard-check"></i> Total Peminjaman</p>
                                </div>
                                <div>
                                    <i class="bi bi-clipboard-check" style="font-size: 3rem; opacity: 0.3;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="mb-0"><?php echo $total_users; ?></h3>
                                    <p class="mb-0"><i class="bi bi-people"></i> Total Peminjam</p>
                                </div>
                                <div>
                                    <i class="bi bi-people" style="font-size: 3rem; opacity: 0.3;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="mb-0"><?php echo $pending; ?></h3>
                                    <p class="mb-0"><i class="bi bi-clock-history"></i> Pending Approval</p>
                                </div>
                                <div>
                                    <i class="bi bi-clock-history" style="font-size: 3rem; opacity: 0.3;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="mb-3"><i class="bi bi-info-circle text-primary"></i> Informasi Sistem</h5>
                            <p class="text-muted mb-0">Gunakan menu di sebelah kiri untuk mengelola sistem peminjaman alat event. Anda dapat mengelola alat, kategori, peminjaman, pengembalian, dan user.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
