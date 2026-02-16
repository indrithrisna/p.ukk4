<?php
session_start();
require_once '../config/database.php';

if (!isLoggedIn() || !hasRole('peminjam')) {
    header("Location: ../auth/login.php");
    exit();
}

// Handle submit peminjaman
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_peminjaman'])) {
    $tanggal_pinjam = clean($_POST['tanggal_pinjam']);
    $tanggal_kembali = clean($_POST['tanggal_kembali']);
    $keterangan = clean($_POST['keterangan']);
    $alat_items = $_POST['alat'] ?? [];
    
    if (empty($alat_items)) {
        $error = "Pilih minimal 1 alat untuk dipinjam!";
    } else {
        // Hitung total biaya
        $total_biaya = 0;
        $hari = (strtotime($tanggal_kembali) - strtotime($tanggal_pinjam)) / (60 * 60 * 24);
        
        foreach ($alat_items as $alat_id => $jumlah) {
            if ($jumlah > 0) {
                $alat = mysqli_fetch_assoc(mysqli_query($conn, "SELECT harga_sewa FROM alat WHERE id = $alat_id"));
                $total_biaya += $alat['harga_sewa'] * $jumlah * $hari;
            }
        }
        
        // Kompatibilitas skema: beberapa DB lama masih punya kolom wajib nama_alat/jumlah di tabel peminjaman
        $nama_alat_list = [];
        $total_jumlah_alat = 0;
        foreach ($alat_items as $alat_id => $jumlah) {
            $jumlah = (int)$jumlah;
            if ($jumlah > 0) {
                $alat_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nama_alat FROM alat WHERE id = $alat_id"));
                if ($alat_info) {
                    $nama_alat_list[] = $alat_info['nama_alat'];
                    $total_jumlah_alat += $jumlah;
                }
            }
        }
        $nama_alat_ringkas = clean(implode(', ', $nama_alat_list));

        $columns = "peminjam_id, tanggal_pinjam, tanggal_kembali, total_biaya, keterangan, status";
        $values = "{$_SESSION['user_id']}, '$tanggal_pinjam', '$tanggal_kembali', $total_biaya, '$keterangan', 'pending'";

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
        foreach ($alat_items as $alat_id => $jumlah) {
            if ($jumlah > 0) {
                $alat = mysqli_fetch_assoc(mysqli_query($conn, "SELECT harga_sewa FROM alat WHERE id = $alat_id"));
                $subtotal = $alat['harga_sewa'] * $jumlah * $hari;
                
                $detail_query = "INSERT INTO detail_peminjaman (peminjaman_id, alat_id, jumlah, harga_satuan, subtotal) 
                                VALUES ($peminjaman_id, $alat_id, $jumlah, {$alat['harga_sewa']}, $subtotal)";
                mysqli_query($conn, $detail_query);
            }
        }
        
        $success = "Peminjaman berhasil diajukan! Menunggu persetujuan petugas.";
    }
}

// Filter kategori
$filter_kategori = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;

$page_title = "Daftar Alat";
include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/peminjam_sidebar.php'; ?>
        <div class="col-md-10 p-4">
            <h2>Daftar Alat Event</h2>
            <hr>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle"></i> <?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <form method="POST" id="formPeminjaman">
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-calendar-check"></i> Informasi Peminjaman</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label">Tanggal Pinjam <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_pinjam" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tanggal Kembali <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_kembali" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Keterangan</label>
                                <input type="text" name="keterangan" class="form-control" placeholder="Keperluan acara...">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-box-seam"></i> Pilih Alat</h5>
                        <div>
                            <select class="form-select form-select-sm" onchange="window.location.href='?kategori='+this.value" style="width: 200px;">
                                <option value="0">Semua Kategori</option>
                                <?php
                                $kat_result = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama_kategori");
                                while ($kat = mysqli_fetch_assoc($kat_result)):
                                ?>
                                <option value="<?php echo $kat['id']; ?>" <?php echo $filter_kategori == $kat['id'] ? 'selected' : ''; ?>>
                                    <?php echo $kat['nama_kategori']; ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="20%">Nama Alat</th>
                                        <th width="10%">Merk</th>
                                        <th width="12%">Kategori</th>
                                        <th width="10%">Tersedia</th>
                                        <th width="10%">Kondisi</th>
                                        <th width="13%">Harga/Hari</th>
                                        <th width="20%">Jumlah Pinjam</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $where = "a.jumlah_tersedia > 0";
                                    if ($filter_kategori > 0) {
                                        $where .= " AND a.kategori_id = $filter_kategori";
                                    }
                                    
                                    $query = "SELECT a.*, k.nama_kategori FROM alat a 
                                             LEFT JOIN kategori k ON a.kategori_id = k.id 
                                             WHERE $where
                                             ORDER BY a.id ASC";
                                    $result = mysqli_query($conn, $query);
                                    $no = 1;
                                    
                                    if (mysqli_num_rows($result) == 0):
                                    ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">Tidak ada alat tersedia</td>
                                    </tr>
                                    <?php else: ?>
                                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td>
                                            <strong><?php echo $row['nama_alat']; ?></strong>
                                            <?php if ($row['deskripsi']): ?>
                                            <br><small class="text-muted"><?php echo substr($row['deskripsi'], 0, 50); ?>...</small>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="badge bg-secondary"><?php echo $row['merk'] ?? '-'; ?></span></td>
                                        <td><span class="badge bg-info"><?php echo $row['nama_kategori']; ?></span></td>
                                        <td><strong class="text-success"><?php echo $row['jumlah_tersedia']; ?></strong> unit</td>
                                        <td>
                                            <span class="badge bg-<?php echo $row['kondisi'] == 'baik' ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst($row['kondisi']); ?>
                                            </span>
                                        </td>
                                        <td><strong>Rp <?php echo number_format($row['harga_sewa'], 0, ',', '.'); ?></strong></td>
                                        <td>
                                            <input type="number" 
                                                   name="alat[<?php echo $row['id']; ?>]" 
                                                   class="form-control form-control-sm" 
                                                   min="0" 
                                                   max="<?php echo $row['jumlah_tersedia']; ?>" 
                                                   value="0"
                                                   placeholder="0">
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="text-end mt-3">
                            <button type="submit" name="submit_peminjaman" class="btn btn-primary btn-lg">
                                <i class="bi bi-send"></i> Ajukan Peminjaman
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>


