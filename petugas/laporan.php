<?php
session_start();
require_once '../config/database.php';

if (!isLoggedIn() || !hasRole('petugas')) {
    header("Location: ../auth/login.php");
    exit();
}

// Statistik Peminjaman
$total_peminjaman = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman"))['total'];
$pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman WHERE status='pending'"))['total'];
$disetujui = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman WHERE status='disetujui'"))['total'];
$dipinjam = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman WHERE status='dipinjam'"))['total'];
$selesai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman WHERE status='selesai'"))['total'];
$ditolak = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman WHERE status='ditolak'"))['total'];

// Statistik Alat
$total_alat = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM alat"))['total'];
$total_stok = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(jumlah_total) as total FROM alat"))['total'];
$stok_tersedia = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(jumlah_tersedia) as total FROM alat"))['total'];
$stok_dipinjam = $total_stok - $stok_tersedia;

// Statistik User
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users"))['total'];
$total_admin = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='admin'"))['total'];
$total_petugas = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='petugas'"))['total'];
$total_peminjam = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='peminjam'"))['total'];

// Statistik Keuangan
$total_pendapatan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_biaya) as total FROM peminjaman WHERE status='selesai'"))['total'] ?? 0;
$total_denda = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(denda) as total FROM peminjaman WHERE denda > 0"))['total'] ?? 0;
$total_keseluruhan = $total_pendapatan + $total_denda;

// Alat Paling Sering Dipinjam
$alat_populer = mysqli_query($conn, "
    SELECT a.nama_alat, COUNT(dp.id) as jumlah_peminjaman, SUM(dp.jumlah) as total_unit
    FROM detail_peminjaman dp
    JOIN alat a ON dp.alat_id = a.id
    JOIN peminjaman p ON dp.peminjaman_id = p.id
    WHERE p.status IN ('disetujui', 'dipinjam', 'selesai')
    GROUP BY a.id
    ORDER BY jumlah_peminjaman DESC
    LIMIT 5
");

// Peminjam Teraktif
$peminjam_aktif = mysqli_query($conn, "
    SELECT u.nama, COUNT(p.id) as jumlah_peminjaman, SUM(p.total_biaya) as total_biaya
    FROM peminjaman p
    JOIN users u ON p.peminjam_id = u.id
    GROUP BY u.id
    ORDER BY jumlah_peminjaman DESC
    LIMIT 5
");

$alat_populer_data = [];
while ($row = mysqli_fetch_assoc($alat_populer)) {
    $alat_populer_data[] = $row;
}

$peminjam_aktif_data = [];
while ($row = mysqli_fetch_assoc($peminjam_aktif)) {
    $peminjam_aktif_data[] = $row;
}

$page_title = "Laporan & Statistik";
include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/petugas_sidebar.php'; ?>
        <div class="col-md-10 p-4">
            <h2><i class="bi bi-graph-up"></i> Laporan & Statistik Sistem</h2>
            <hr>

            <div class="row g-3 mb-4">
                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="bi bi-pie-chart-fill"></i> Status Peminjaman</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="chartStatusPeminjaman" height="220"></canvas>
                            <div class="small text-muted mt-3">
                                Pending <?php echo $total_peminjaman > 0 ? round(($pending / $total_peminjaman) * 100, 1) : 0; ?>% |
                                Disetujui <?php echo $total_peminjaman > 0 ? round(($disetujui / $total_peminjaman) * 100, 1) : 0; ?>% |
                                Dipinjam <?php echo $total_peminjaman > 0 ? round(($dipinjam / $total_peminjaman) * 100, 1) : 0; ?>% |
                                Selesai <?php echo $total_peminjaman > 0 ? round(($selesai / $total_peminjaman) * 100, 1) : 0; ?>% |
                                Ditolak <?php echo $total_peminjaman > 0 ? round(($ditolak / $total_peminjaman) * 100, 1) : 0; ?>%
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0"><i class="bi bi-box-seam"></i> Komposisi Stok Alat</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="chartStokAlat" height="220"></canvas>
                            <div class="small text-muted mt-3">
                                Tersedia <?php echo $total_stok > 0 ? round(($stok_tersedia / $total_stok) * 100, 1) : 0; ?>% |
                                Dipinjam <?php echo $total_stok > 0 ? round(($stok_dipinjam / $total_stok) * 100, 1) : 0; ?>%
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-header bg-dark text-white">
                            <h6 class="mb-0"><i class="bi bi-people-fill"></i> Komposisi User</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="chartKomposisiUser" height="220"></canvas>
                            <div class="small text-muted mt-3">
                                Admin <?php echo $total_users > 0 ? round(($total_admin / $total_users) * 100, 1) : 0; ?>% |
                                Petugas <?php echo $total_users > 0 ? round(($total_petugas / $total_users) * 100, 1) : 0; ?>% |
                                Peminjam <?php echo $total_users > 0 ? round(($total_peminjam / $total_users) * 100, 1) : 0; ?>%
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header bg-warning text-white">
                            <h6 class="mb-0"><i class="bi bi-trophy-fill"></i> Top Alat Populer</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="chartTopAlat" height="220"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><i class="bi bi-star-fill"></i> Top Peminjam Aktif</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="chartTopPeminjam" height="220"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tabel Statistik -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="bi bi-trophy"></i> Top 5 Alat Paling Populer</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Nama Alat</th>
                                        <th class="text-center">Peminjaman</th>
                                        <th class="text-center">Total Unit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    if (!empty($alat_populer_data)):
                                        foreach ($alat_populer_data as $row): 
                                    ?>
                                    <tr>
                                        <td><?php echo $row['nama_alat']; ?></td>
                                        <td class="text-center"><span class="badge bg-primary"><?php echo $row['jumlah_peminjaman']; ?>x</span></td>
                                        <td class="text-center"><?php echo $row['total_unit']; ?> unit</td>
                                    </tr>
                                    <?php 
                                        endforeach;
                                    else:
                                    ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">Belum ada data</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0"><i class="bi bi-star"></i> Top 5 Peminjam Teraktif</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Nama Peminjam</th>
                                        <th class="text-center">Peminjaman</th>
                                        <th class="text-end">Total Biaya</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    if (!empty($peminjam_aktif_data)):
                                        foreach ($peminjam_aktif_data as $row): 
                                    ?>
                                    <tr>
                                        <td><?php echo $row['nama']; ?></td>
                                        <td class="text-center"><span class="badge bg-success"><?php echo $row['jumlah_peminjaman']; ?>x</span></td>
                                        <td class="text-end">Rp <?php echo number_format($row['total_biaya'], 0, ',', '.'); ?></td>
                                    </tr>
                                    <?php 
                                        endforeach;
                                    else:
                                    ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">Belum ada data</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const withPercent = (context) => {
            const label = context.label || '';
            const value = Number(context.raw || 0);
            const data = context.dataset.data || [];
            const total = data.reduce((sum, item) => sum + Number(item || 0), 0);
            const percent = total > 0 ? ((value / total) * 100).toFixed(1) : '0.0';
            return `${label}: ${value} (${percent}%)`;
        };

        const statusData = [<?php echo (int)$pending; ?>, <?php echo (int)$disetujui; ?>, <?php echo (int)$dipinjam; ?>, <?php echo (int)$selesai; ?>, <?php echo (int)$ditolak; ?>];
        const stokData = [<?php echo (int)$stok_tersedia; ?>, <?php echo (int)$stok_dipinjam; ?>];
        const userData = [<?php echo (int)$total_admin; ?>, <?php echo (int)$total_petugas; ?>, <?php echo (int)$total_peminjam; ?>];
        const topAlatLabels = <?php echo json_encode(array_column($alat_populer_data, 'nama_alat')); ?>;
        const topAlatData = <?php echo json_encode(array_map('intval', array_column($alat_populer_data, 'jumlah_peminjaman'))); ?>;
        const topPeminjamLabels = <?php echo json_encode(array_column($peminjam_aktif_data, 'nama')); ?>;
        const topPeminjamData = <?php echo json_encode(array_map('intval', array_column($peminjam_aktif_data, 'jumlah_peminjaman'))); ?>;

        new Chart(document.getElementById('chartStatusPeminjaman'), {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'Disetujui', 'Dipinjam', 'Selesai', 'Ditolak'],
                datasets: [{
                    data: statusData,
                    backgroundColor: ['#f6c23e', '#36b9cc', '#6c757d', '#1cc88a', '#e74a3b'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: { callbacks: { label: withPercent } }
                }
            }
        });

        new Chart(document.getElementById('chartStokAlat'), {
            type: 'pie',
            data: {
                labels: ['Tersedia', 'Dipinjam'],
                datasets: [{
                    data: stokData,
                    backgroundColor: ['#1cc88a', '#f6c23e'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: { callbacks: { label: withPercent } }
                }
            }
        });

        new Chart(document.getElementById('chartKomposisiUser'), {
            type: 'polarArea',
            data: {
                labels: ['Admin', 'Petugas', 'Peminjam'],
                datasets: [{
                    data: userData,
                    backgroundColor: ['#e74a3b', '#4e73df', '#1cc88a'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: { callbacks: { label: withPercent } }
                }
            }
        });

        new Chart(document.getElementById('chartTopAlat'), {
            type: 'bar',
            data: {
                labels: topAlatLabels,
                datasets: [{
                    label: 'Jumlah Peminjaman',
                    data: topAlatData,
                    backgroundColor: '#4e73df',
                    borderRadius: 10
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: withPercent } }
                },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });

        new Chart(document.getElementById('chartTopPeminjam'), {
            type: 'bar',
            data: {
                labels: topPeminjamLabels,
                datasets: [{
                    label: 'Jumlah Peminjaman',
                    data: topPeminjamData,
                    backgroundColor: '#1cc88a',
                    borderRadius: 10
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: withPercent } }
                },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    });
</script>

<?php include '../includes/footer.php'; ?>


