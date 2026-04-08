<?php
session_start();
require_once '../config/database.php';
require_once '../controllers/PeminjamanController.php';

if (!isLoggedIn() || !hasRole('petugas')) {
    header("Location: ../auth/login.php");
    exit();
}

$controller = new PeminjamanController($conn);

if (isset($_GET['action']) && isset($_GET['id'])) {
    $id     = (int)$_GET['id'];
    $action = $_GET['action'];
    if ($action == 'approve') $controller->dipinjam($id, 'petugas');
    if ($action == 'reject')  $controller->tolak($id, 'petugas');
}

$peminjaman_list = $controller->index();
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
                                <th>Tgl Pinjam</th>
                                <th>Tgl Kembali</th>
                                <th>Status</th>
                                <th>Total Biaya</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($peminjaman_list as $row): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['tanggal_pinjam'])); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['tanggal_kembali'])); ?></td>
                                <td><span class="badge bg-<?php echo $row['status']=='pending'?'warning':'success'; ?>"><?php echo $row['status']; ?></span></td>
                                <td>Rp <?php echo number_format($row['total_biaya'], 0, ',', '.'); ?></td>
                                <td>
                                    <?php if ($row['status'] == 'pending'): ?>
                                    <button class="btn btn-sm btn-success" onclick="if(confirm('Setujui peminjaman ini?')) location.href='?action=approve&id=<?php echo $row['id']; ?>'">Setujui</button>
                                    <button class="btn btn-sm btn-danger" onclick="if(confirm('Tolak peminjaman ini?')) location.href='?action=reject&id=<?php echo $row['id']; ?>'">Tolak</button>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
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
