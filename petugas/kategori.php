<?php
session_start();
require_once '../config/database.php';

if (!isLoggedIn() || !hasRole('petugas')) {
    header("Location: ../auth/login.php");
    exit();
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM kategori WHERE id = $id");
    header("Location: kategori.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $nama_kategori = clean($_POST['nama_kategori']);
    $deskripsi = clean($_POST['deskripsi']);
    
    if ($id > 0) {
        $query = "UPDATE kategori SET nama_kategori='$nama_kategori', deskripsi='$deskripsi' WHERE id=$id";
    } else {
        $query = "INSERT INTO kategori (nama_kategori, deskripsi) VALUES ('$nama_kategori', '$deskripsi')";
    }
    
    mysqli_query($conn, $query);
    header("Location: kategori.php");
    exit();
}

$page_title = "Kelola Kategori";
include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/petugas_sidebar.php'; ?>
        <div class="col-md-10 p-4">
            <h2>Kelola Kategori</h2>
            <hr>
            
            <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalKategori">
                <i class="bi bi-plus"></i> Tambah Kategori
            </button>
            
            <div class="card">
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama Kategori</th>
                                <th>Deskripsi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result = mysqli_query($conn, "SELECT * FROM kategori ORDER BY id ASC");
                            while ($row = mysqli_fetch_assoc($result)):
                            ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo $row['nama_kategori']; ?></td>
                                <td><?php echo $row['deskripsi']; ?></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-warning" onclick="editKategori(<?php echo $row['id']; ?>, '<?php echo addslashes($row['nama_kategori']); ?>', '<?php echo addslashes($row['deskripsi']); ?>')">
                                        <i class="bi bi-pencil"></i> Edit
                                    </button>
                                    <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus kategori <?php echo addslashes($row['nama_kategori']); ?>?')">
                                        <i class="bi bi-trash"></i> Hapus
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalKategori" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="formKategori">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="kategoriId" value="">
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori</label>
                        <input type="text" name="nama_kategori" id="nama_kategori" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" id="deskripsi_kategori" class="form-control" rows="3"></textarea>
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
function editKategori(id, nama_kategori, deskripsi) {
    document.getElementById('kategoriId').value = id;
    document.getElementById('nama_kategori').value = nama_kategori;
    document.getElementById('deskripsi_kategori').value = deskripsi;
    document.getElementById('modalTitle').textContent = 'Edit Kategori';
    
    var modal = new bootstrap.Modal(document.getElementById('modalKategori'));
    modal.show();
}

// Reset form when adding new kategori
document.querySelector('[data-bs-target="#modalKategori"]').addEventListener('click', function() {
    document.getElementById('formKategori').reset();
    document.getElementById('kategoriId').value = '';
    document.getElementById('modalTitle').textContent = 'Tambah Kategori';
});
</script>

<?php include '../includes/footer.php'; ?>


