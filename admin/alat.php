<?php
session_start();
require_once '../config/database.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header("Location: ../auth/login.php");
    exit();
}

// Handle Delete (Soft Delete)
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $alat = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nama_alat FROM alat WHERE id=$id"));
    // Soft delete - hanya tandai sebagai deleted
    mysqli_query($conn, "UPDATE alat SET deleted_at = NOW() WHERE id = $id");
    logActivity($_SESSION['user_id'], 'Hapus Alat', "Menghapus alat: {$alat['nama_alat']}");
    header("Location: alat.php");
    exit();
}

// Handle Restore
if (isset($_GET['restore'])) {
    $id = (int)$_GET['restore'];
    $alat = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nama_alat FROM alat WHERE id=$id"));
    // Restore - hapus tanda deleted
    mysqli_query($conn, "UPDATE alat SET deleted_at = NULL WHERE id = $id");
    logActivity($_SESSION['user_id'], 'Restore Alat', "Mengembalikan alat: {$alat['nama_alat']}");
    header("Location: alat.php?show=deleted");
    exit();
}

// Handle Permanent Delete
if (isset($_GET['permanent_delete'])) {
    $id = (int)$_GET['permanent_delete'];
    $alat = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nama_alat FROM alat WHERE id=$id"));
    // Permanent delete - hapus dari database
    mysqli_query($conn, "DELETE FROM alat WHERE id = $id");
    logActivity($_SESSION['user_id'], 'Hapus Permanen Alat', "Menghapus permanen alat: {$alat['nama_alat']}");
    header("Location: alat.php?show=deleted");
    exit();
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $nama_alat = clean($_POST['nama_alat']);
    $kategori_id = (int)$_POST['kategori_id'];
    $jumlah_total = (int)$_POST['jumlah_total'];
    $jumlah_tersedia = (int)$_POST['jumlah_tersedia'];
    $kondisi = clean($_POST['kondisi']);
    $harga_sewa = (float)$_POST['harga_sewa'];
    $deskripsi = clean($_POST['deskripsi']);
    
    if ($id > 0) {
        $query = "UPDATE alat SET nama_alat='$nama_alat', kategori_id=$kategori_id, 
                  jumlah_total=$jumlah_total, jumlah_tersedia=$jumlah_tersedia, 
                  kondisi='$kondisi', harga_sewa=$harga_sewa, deskripsi='$deskripsi' 
                  WHERE id=$id";
        logActivity($_SESSION['user_id'], 'Update Alat', "Mengubah data alat: $nama_alat");
    } else {
        $query = "INSERT INTO alat (nama_alat, kategori_id, jumlah_total, jumlah_tersedia, kondisi, harga_sewa, deskripsi) 
                  VALUES ('$nama_alat', $kategori_id, $jumlah_total, $jumlah_tersedia, '$kondisi', $harga_sewa, '$deskripsi')";
        logActivity($_SESSION['user_id'], 'Tambah Alat', "Menambah alat baru: $nama_alat");
    }
    
    mysqli_query($conn, $query);
    header("Location: alat.php");
    exit();
}

$page_title = "Kelola Alat";
include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/admin_sidebar.php'; ?>
        
        <div class="col-md-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">Kelola Alat Event</h2>
                    <p class="text-muted mb-0">Manajemen data alat dan inventori</p>
                </div>
            </div>
            
            <?php
            $show_deleted = isset($_GET['show']) && $_GET['show'] == 'deleted';
            ?>
            
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAlat">
                            <i class="bi bi-plus-circle"></i> Tambah Alat
                        </button>
                        
                        <?php if (!$show_deleted): ?>
                        <a href="?show=deleted" class="btn btn-outline-info">
                            <i class="bi bi-archive"></i> Lihat Alat Terhapus
                        </a>
                        <?php else: ?>
                        <a href="alat.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali ke Daftar Alat
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <?php if ($show_deleted): ?>
            <div class="alert alert-warning">
                <i class="bi bi-info-circle"></i> Menampilkan alat yang sudah dihapus. Anda bisa restore atau hapus permanen.
            </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Alat</th>
                                    <th>Kategori</th>
                                    <th>Jumlah</th>
                                    <th>Tersedia</th>
                                    <th>Kondisi</th>
                                    <th>Harga Sewa</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Query berdasarkan filter
                                if ($show_deleted) {
                                    $query = "SELECT a.*, k.nama_kategori FROM alat a 
                                             LEFT JOIN kategori k ON a.kategori_id = k.id 
                                             WHERE a.deleted_at IS NOT NULL
                                             ORDER BY a.deleted_at DESC";
                                } else {
                                    $query = "SELECT a.*, k.nama_kategori FROM alat a 
                                             LEFT JOIN kategori k ON a.kategori_id = k.id 
                                             WHERE a.deleted_at IS NULL
                                             ORDER BY a.id ASC";
                                }
                                $result = mysqli_query($conn, $query);
                                
                                if (mysqli_num_rows($result) == 0):
                                ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                                        <p class="mt-2 mb-0">
                                            <?php if ($show_deleted): ?>
                                            Tidak ada alat yang dihapus
                                            <?php else: ?>
                                            Belum ada data alat
                                            <?php endif; ?>
                                        </p>
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php 
                                $no = 1;
                                while ($row = mysqli_fetch_assoc($result)): 
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><strong><?php echo $row['nama_alat']; ?></strong></td>
                                    <td><?php echo $row['nama_kategori']; ?></td>
                                    <td><?php echo $row['jumlah_total']; ?></td>
                                    <td>
                                        <?php if ($row['jumlah_tersedia'] == 0): ?>
                                        <span class="badge bg-danger">Habis</span>
                                        <?php else: ?>
                                        <span class="badge bg-success"><?php echo $row['jumlah_tersedia']; ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-<?php echo $row['kondisi'] == 'baik' ? 'success' : 'warning'; ?>"><?php echo ucfirst($row['kondisi']); ?></span></td>
                                    <td><strong>Rp <?php echo number_format($row['harga_sewa'], 0, ',', '.'); ?></strong></td>
                                    <td>
                                        <?php if ($show_deleted): ?>
                                            <button type="button" class="btn btn-sm btn-success" onclick="if(confirm('Restore alat <?php echo addslashes($row['nama_alat']); ?>?')) window.location.href='?restore=<?php echo $row['id']; ?>'">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="if(confirm('HAPUS PERMANEN alat <?php echo addslashes($row['nama_alat']); ?>? Data tidak bisa dikembalikan!')) window.location.href='?permanent_delete=<?php echo $row['id']; ?>'">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-warning" onclick="editAlat(<?php echo $row['id']; ?>, '<?php echo addslashes($row['nama_alat']); ?>', <?php echo $row['kategori_id']; ?>, <?php echo $row['jumlah_total']; ?>, <?php echo $row['jumlah_tersedia']; ?>, '<?php echo $row['kondisi']; ?>', <?php echo $row['harga_sewa']; ?>, '<?php echo addslashes($row['deskripsi']); ?>')">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus alat <?php echo addslashes($row['nama_alat']); ?>?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalAlat" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="formAlat">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Alat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="alatId" value="">
                    <div class="mb-3">
                        <label class="form-label">Nama Alat</label>
                        <input type="text" name="nama_alat" id="nama_alat" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <select name="kategori_id" id="kategori_id" class="form-control" required>
                            <?php
                            $kat = mysqli_query($conn, "SELECT * FROM kategori ORDER BY id ASC");
                            while ($k = mysqli_fetch_assoc($kat)):
                            ?>
                            <option value="<?php echo $k['id']; ?>"><?php echo $k['nama_kategori']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah Total</label>
                        <input type="number" name="jumlah_total" id="jumlah_total" class="form-control" required min="0">
                        <small class="text-muted">Bisa input 0 untuk stok kosong</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah Tersedia</label>
                        <input type="number" name="jumlah_tersedia" id="jumlah_tersedia" class="form-control" required min="0">
                        <small class="text-muted">Bisa input 0 untuk stok kosong</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kondisi</label>
                        <select name="kondisi" id="kondisi" class="form-control" required>
                            <option value="baik">Baik</option>
                            <option value="rusak ringan">Rusak Ringan</option>
                            <option value="rusak berat">Rusak Berat</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Harga Sewa (per hari)</label>
                        <input type="number" name="harga_sewa" id="harga_sewa" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" id="deskripsi" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editAlat(id, nama_alat, kategori_id, jumlah_total, jumlah_tersedia, kondisi, harga_sewa, deskripsi) {
    document.getElementById('alatId').value = id;
    document.getElementById('nama_alat').value = nama_alat;
    document.getElementById('kategori_id').value = kategori_id;
    document.getElementById('jumlah_total').value = jumlah_total;
    document.getElementById('jumlah_tersedia').value = jumlah_tersedia;
    document.getElementById('kondisi').value = kondisi;
    document.getElementById('harga_sewa').value = harga_sewa;
    document.getElementById('deskripsi').value = deskripsi;
    document.getElementById('modalTitle').textContent = 'Edit Alat';
    
    var modal = new bootstrap.Modal(document.getElementById('modalAlat'));
    modal.show();
}

// Reset form when adding new alat
document.querySelector('[data-bs-target="#modalAlat"]').addEventListener('click', function() {
    document.getElementById('formAlat').reset();
    document.getElementById('alatId').value = '';
    document.getElementById('modalTitle').textContent = 'Tambah Alat';
});
</script>

<?php include '../includes/footer.php'; ?>
