<?php
session_start();
require_once '../config/database.php';

if (!isLoggedIn() || !hasRole('petugas')) {
    header("Location: ../auth/login.php");
    exit();
}

// Handle update pengaturan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $denda_per_hari = (float)$_POST['denda_per_hari'];
    $denda_rusak_ringan = (float)$_POST['denda_rusak_ringan'];
    $denda_rusak_berat = (float)$_POST['denda_rusak_berat'];
    $denda_hilang_persen = (int)$_POST['denda_hilang_persen'];
    
    $query = "UPDATE pengaturan_denda SET 
              denda_per_hari = $denda_per_hari,
              denda_rusak_ringan = $denda_rusak_ringan,
              denda_rusak_berat = $denda_rusak_berat,
              denda_hilang_persen = $denda_hilang_persen
              WHERE id = 1";
    
    if (mysqli_query($conn, $query)) {
        logActivity($_SESSION['user_id'], 'Update Pengaturan Denda', 'Mengubah pengaturan denda sistem');
        $success = "Pengaturan denda berhasil diperbarui!";
    }
}

// Get pengaturan
$pengaturan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM pengaturan_denda LIMIT 1"));
if (!$pengaturan) {
    mysqli_query($conn, "INSERT INTO pengaturan_denda (denda_per_hari, denda_rusak_ringan, denda_rusak_berat, denda_hilang_persen) VALUES (10000, 50000, 100000, 100)");
    $pengaturan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM pengaturan_denda LIMIT 1"));
}

$page_title = "Pengaturan Denda";
include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/petugas_sidebar.php'; ?>
        <div class="col-md-10 p-4">
            <h2>Pengaturan Denda</h2>
            <p class="text-muted">Atur besaran denda untuk keterlambatan dan kerusakan alat</p>
            <hr>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle"></i> <?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bi bi-gear"></i> Pengaturan Denda</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-4">
                                    <label class="form-label"><strong>Denda Keterlambatan (per hari)</strong></label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" name="denda_per_hari" class="form-control" 
                                               value="<?php echo $pengaturan['denda_per_hari']; ?>" required>
                                    </div>
                                    <small class="text-muted">Denda yang dikenakan untuk setiap hari keterlambatan pengembalian</small>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label"><strong>Denda Rusak Ringan</strong></label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" name="denda_rusak_ringan" class="form-control" 
                                               value="<?php echo $pengaturan['denda_rusak_ringan']; ?>" required>
                                    </div>
                                    <small class="text-muted">Denda untuk alat yang dikembalikan dalam kondisi rusak ringan</small>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label"><strong>Denda Rusak Berat</strong></label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" name="denda_rusak_berat" class="form-control" 
                                               value="<?php echo $pengaturan['denda_rusak_berat']; ?>" required>
                                    </div>
                                    <small class="text-muted">Denda untuk alat yang dikembalikan dalam kondisi rusak berat</small>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label"><strong>Denda Alat Hilang (% dari harga sewa)</strong></label>
                                    <div class="input-group">
                                        <input type="number" name="denda_hilang_persen" class="form-control" 
                                               value="<?php echo $pengaturan['denda_hilang_persen']; ?>" 
                                               min="0" max="200" required>
                                        <span class="input-group-text">%</span>
                                    </div>
                                    <small class="text-muted">Persentase dari total biaya sewa yang dikenakan jika alat hilang</small>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Simpan Pengaturan
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="bi bi-info-circle"></i> Informasi</h6>
                        </div>
                        <div class="card-body">
                            <h6>Contoh Perhitungan:</h6>
                            <ul class="small">
                                <li><strong>Keterlambatan 3 hari:</strong><br>
                                    3 × Rp <?php echo number_format($pengaturan['denda_per_hari'], 0, ',', '.'); ?> = 
                                    Rp <?php echo number_format($pengaturan['denda_per_hari'] * 3, 0, ',', '.'); ?>
                                </li>
                                <li><strong>Rusak Ringan:</strong><br>
                                    Rp <?php echo number_format($pengaturan['denda_rusak_ringan'], 0, ',', '.'); ?>
                                </li>
                                <li><strong>Rusak Berat:</strong><br>
                                    Rp <?php echo number_format($pengaturan['denda_rusak_berat'], 0, ',', '.'); ?>
                                </li>
                                <li><strong>Hilang (Sewa Rp 100.000):</strong><br>
                                    <?php echo $pengaturan['denda_hilang_persen']; ?>% × Rp 100.000 = 
                                    Rp <?php echo number_format(100000 * $pengaturan['denda_hilang_persen'] / 100, 0, ',', '.'); ?>
                                </li>
                            </ul>
                            
                            <hr>
                            
                            <h6>Catatan:</h6>
                            <ul class="small">
                                <li>Denda keterlambatan dan denda kondisi akan dijumlahkan</li>
                                <li>Pengaturan ini berlaku untuk semua peminjaman</li>
                                <li>Perubahan tidak mempengaruhi peminjaman yang sudah selesai</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>


