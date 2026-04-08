<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Sistem Peminjaman Alat Event'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>assets/css/style.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background: #343a40;
        }
        .sidebar a {
            color: #fff;
            text-decoration: none;
            padding: 10px 20px;
            display: block;
        }
        .sidebar a:hover {
            background: #495057;
        }
        .sidebar a.active {
            background: #007bff;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container-fluid">
            <?php
            // Base path sudah didefinisikan di config/database.php
            // Notifikasi untuk Peminjam
            $notif_count = 0;
            $notifications = [];
            
            if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
                if ($_SESSION['role'] == 'peminjam') {
                    // Cek peminjaman yang hampir jatuh tempo (H-2)
                    $user_id = $_SESSION['user_id'];
                    $query = "SELECT p.*, 
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
                    $result = mysqli_query($conn, $query);
                    while ($row = mysqli_fetch_assoc($result)) {
                        $notifications[] = [
                            'type' => 'warning',
                            'icon' => 'exclamation-triangle',
                            'title' => 'Pengingat Pengembalian',
                            'message' => "Alat {$row['alat_list']} harus dikembalikan dalam {$row['hari_tersisa']} hari (Tanggal: " . date('d/m/Y', strtotime($row['tanggal_kembali'])) . ")",
                            'time' => 'Segera'
                        ];
                        $notif_count++;
                    }
                    
                    // Cek peminjaman yang sudah terlambat
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
                    while ($row = mysqli_fetch_assoc($result_late)) {
                        $notifications[] = [
                            'type' => 'danger',
                            'icon' => 'exclamation-circle',
                            'title' => 'Terlambat!',
                            'message' => "Alat {$row['alat_list']} sudah terlambat {$row['hari_terlambat']} hari! Segera kembalikan untuk menghindari denda.",
                            'time' => 'Urgent'
                        ];
                        $notif_count++;
                    }
                } elseif ($_SESSION['role'] == 'petugas' || $_SESSION['role'] == 'admin') {
                    // Cek semua peminjaman yang hampir jatuh tempo
                    $query = "SELECT p.*, u.nama,
                             DATEDIFF(p.tanggal_kembali, CURDATE()) as hari_tersisa,
                             (SELECT GROUP_CONCAT(a.nama_alat SEPARATOR ', ') 
                              FROM detail_peminjaman dp 
                              JOIN alat a ON dp.alat_id = a.id 
                              WHERE dp.peminjaman_id = p.id) as alat_list
                             FROM peminjaman p 
                             JOIN users u ON p.peminjam_id = u.id
                             WHERE p.status = 'dipinjam'
                             AND DATEDIFF(p.tanggal_kembali, CURDATE()) <= 2
                             AND DATEDIFF(p.tanggal_kembali, CURDATE()) >= 0
                             ORDER BY p.tanggal_kembali ASC";
                    $result = mysqli_query($conn, $query);
                    while ($row = mysqli_fetch_assoc($result)) {
                        $notifications[] = [
                            'type' => 'warning',
                            'icon' => 'exclamation-triangle',
                            'title' => 'Hampir Jatuh Tempo',
                            'message' => "{$row['nama']} - {$row['alat_list']} ({$row['hari_tersisa']} hari lagi)",
                            'time' => date('d/m/Y', strtotime($row['tanggal_kembali']))
                        ];
                        $notif_count++;
                    }
                    
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
                                  ORDER BY p.tanggal_kembali ASC";
                    $result_late = mysqli_query($conn, $query_late);
                    while ($row = mysqli_fetch_assoc($result_late)) {
                        $notifications[] = [
                            'type' => 'danger',
                            'icon' => 'exclamation-circle',
                            'title' => 'Terlambat!',
                            'message' => "{$row['nama']} - {$row['alat_list']} (Terlambat {$row['hari_terlambat']} hari)",
                            'time' => 'Urgent'
                        ];
                        $notif_count++;
                    }
                    
                    // Cek peminjaman pending
                    $query_pending = "SELECT COUNT(*) as total FROM peminjaman WHERE status = 'pending'";
                    $pending = mysqli_fetch_assoc(mysqli_query($conn, $query_pending))['total'];
                    if ($pending > 0) {
                        $notifications[] = [
                            'type' => 'info',
                            'icon' => 'clock',
                            'title' => 'Peminjaman Pending',
                            'message' => "Ada {$pending} peminjaman menunggu persetujuan",
                            'time' => 'Baru'
                        ];
                        $notif_count++;
                    }
                }
            }
            ?>
            <a class="navbar-brand" href="<?php echo BASE_PATH; ?>index.php">
                <i class="bi bi-box-seam text-primary"></i> Sistem Peminjaman Alat Event
            </a>
            <?php if (isset($_SESSION['user_id'])): ?>
            <div class="navbar-nav ms-auto d-flex align-items-center">
                <!-- Notification Bell -->
                <div class="dropdown me-3">
                    <a class="btn btn-light border position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-bell-fill text-primary"></i>
                        <?php if ($notif_count > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?php echo $notif_count; ?>
                            <span class="visually-hidden">notifikasi baru</span>
                        </span>
                        <?php endif; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" style="width: 350px; max-height: 400px; overflow-y: auto;">
                        <li class="dropdown-header">
                            <strong>Notifikasi (<?php echo $notif_count; ?>)</strong>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <?php if (empty($notifications)): ?>
                        <li class="px-3 py-2 text-center text-muted">
                            <i class="bi bi-check-circle"></i> Tidak ada notifikasi
                        </li>
                        <?php else: ?>
                            <?php foreach ($notifications as $notif): ?>
                            <li>
                                <a class="dropdown-item py-2" href="#">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <i class="bi bi-<?php echo $notif['icon']; ?> text-<?php echo $notif['type']; ?> fs-4"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <strong class="d-block"><?php echo $notif['title']; ?></strong>
                                            <small class="text-muted d-block"><?php echo $notif['message']; ?></small>
                                            <small class="text-<?php echo $notif['type']; ?>"><i class="bi bi-clock"></i> <?php echo $notif['time']; ?></small>
                                        </div>
                                    </div>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <span class="navbar-text text-dark me-3 fw-bold">
                    <i class="bi bi-person-circle text-primary"></i> <?php echo $_SESSION['nama']; ?> 
                    <span class="badge bg-primary"><?php echo ucfirst($_SESSION['role']); ?></span>
                </span>
                <a href="<?php echo BASE_PATH; ?>auth/logout.php" class="btn btn-danger btn-sm">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
            <?php endif; ?>
        </div>
    </nav>
