<?php
session_start();
require_once '../config/database.php';

if (!isLoggedIn() || !hasRole('peminjam')) {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $tanggal_pinjam = clean($_POST['tanggal_pinjam']);
    $tanggal_kembali = clean($_POST['tanggal_kembali']);
    $keterangan = clean($_POST['keterangan']);
    $alat_ids = $_POST['alat_id'];
    $jumlahs = $_POST['jumlah'];
    
    // Hitung total biaya
    $total_biaya = 0;
    $hari = (strtotime($tanggal_kembali) - strtotime($tanggal_pinjam)) / (60 * 60 * 24);
    
    // Kompatibilitas skema: beberapa DB lama masih punya kolom wajib nama_alat/jumlah di tabel peminjaman
    $nama_alat_list = [];
    $total_jumlah_alat = 0;
    foreach ($alat_ids as $key => $alat_id) {
        $jumlah = (int)$jumlahs[$key];
        if ($jumlah > 0) {
            $alat_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nama_alat FROM alat WHERE id = $alat_id"));
            if ($alat_info) {
                $nama_alat_list[] = $alat_info['nama_alat'];
                $total_jumlah_alat += $jumlah;
            }
        }
    }
    $nama_alat_ringkas = clean(implode(', ', $nama_alat_list));

    $columns = "peminjam_id, tanggal_pinjam, tanggal_kembali, keterangan, total_biaya";
    $values = "$user_id, '$tanggal_pinjam', '$tanggal_kembali', '$keterangan', 0";

    $has_nama_alat = mysqli_num_rows(mysqli_query($conn, "SHOW COLUMNS FROM peminjaman LIKE 'nama_alat'")) > 0;
    $has_jumlah = mysqli_num_rows(mysqli_query($conn, "SHOW COLUMNS FROM peminjaman LIKE 'jumlah'")) > 0;

    if ($has_nama_alat) {
        $columns .= ", nama_alat";
        $values .= ", '$nama_alat_ringkas'";
    }
    if ($has_jumlah) {
        $columns .= ", jumlah";
        $values .= ", $total_jumlah_alat";
    }

    // Insert peminjaman
    $query = "INSERT INTO peminjaman ($columns) VALUES ($values)";
    mysqli_query($conn, $query);
    $peminjaman_id = mysqli_insert_id($conn);
    
    // Insert detail peminjaman
    foreach ($alat_ids as $key => $alat_id) {
        $jumlah = (int)$jumlahs[$key];
        if ($jumlah > 0) {
            $alat = mysqli_fetch_assoc(mysqli_query($conn, "SELECT harga_sewa FROM alat WHERE id=$alat_id"));
            $subtotal = $alat['harga_sewa'] * $jumlah * $hari;
            $total_biaya += $subtotal;
            
            mysqli_query($conn, "INSERT INTO detail_peminjaman (peminjaman_id, alat_id, jumlah, harga_satuan, subtotal) 
                                VALUES ($peminjaman_id, $alat_id, $jumlah, {$alat['harga_sewa']}, $subtotal)");
        }
    }
    
    // Update total biaya
    mysqli_query($conn, "UPDATE peminjaman SET total_biaya=$total_biaya WHERE id=$peminjaman_id");
    
    logActivity($_SESSION['user_id'], 'Buat Peminjaman', "Membuat peminjaman baru ID: $peminjaman_id dengan total biaya Rp " . number_format($total_biaya, 0, ',', '.'));
    
    header("Location: peminjaman.php");
    exit();
}

$page_title = "Buat Peminjaman";
include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/peminjam_sidebar.php'; ?>
        <div class="col-md-10 p-4">
            <h2>Ajukan Peminjaman Baru</h2>
            <hr>
            
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Pengajuan peminjaman akan direview oleh petugas. Anda akan mendapat notifikasi jika disetujui atau ditolak.
            </div>
            
            <div class="card">
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Pinjam</label>
                                <input type="date" name="tanggal_pinjam" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Kembali</label>
                                <input type="date" name="tanggal_kembali" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <textarea name="keterangan" class="form-control" rows="3"></textarea>
                        </div>
                        
                        <h5>Pilih Alat</h5>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Nama Alat</th>
                                    <th>Tersedia</th>
                                    <th>Harga/hari</th>
                                    <th>Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $result = mysqli_query($conn, "SELECT * FROM alat WHERE jumlah_tersedia > 0");
                                while ($row = mysqli_fetch_assoc($result)):
                                ?>
                                <tr>
                                    <td><?php echo $row['nama_alat']; ?></td>
                                    <td><?php echo $row['jumlah_tersedia']; ?></td>
                                    <td>Rp <?php echo number_format($row['harga_sewa'], 0, ',', '.'); ?></td>
                                    <td>
                                        <input type="hidden" name="alat_id[]" value="<?php echo $row['id']; ?>">
                                        <input type="number" name="jumlah[]" class="form-control" min="0" max="<?php echo $row['jumlah_tersedia']; ?>" value="0">
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                        
                        <button type="submit" class="btn btn-primary">Ajukan Peminjaman</button>
                        <a href="peminjaman.php" class="btn btn-secondary">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>


