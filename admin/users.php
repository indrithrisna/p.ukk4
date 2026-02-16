<?php
session_start();
require_once '../config/database.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header("Location: ../auth/login.php");
    exit();
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nama FROM users WHERE id=$id"));
    mysqli_query($conn, "DELETE FROM users WHERE id = $id");
    logActivity($_SESSION['user_id'], 'Hapus User', "Menghapus user: {$user['nama']}");
    header("Location: users.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $username = clean($_POST['username']);
    $nama = clean($_POST['nama']);
    $email = clean($_POST['email']);
    $telepon = clean($_POST['telepon']);
    $role = clean($_POST['role']);
    
    if ($id > 0) {
        $query = "UPDATE users SET username='$username', nama='$nama', email='$email', telepon='$telepon', role='$role' WHERE id=$id";
        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $query = "UPDATE users SET username='$username', password='$password', nama='$nama', email='$email', telepon='$telepon', role='$role' WHERE id=$id";
        }
        logActivity($_SESSION['user_id'], 'Update User', "Mengubah data user: $nama");
    } else {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $query = "INSERT INTO users (username, password, nama, email, telepon, role) 
                  VALUES ('$username', '$password', '$nama', '$email', '$telepon', '$role')";
        logActivity($_SESSION['user_id'], 'Tambah User', "Menambah user baru: $nama ($role)");
    }
    
    mysqli_query($conn, $query);
    header("Location: users.php");
    exit();
}

$page_title = "Kelola User";
include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/admin_sidebar.php'; ?>
        
        <div class="col-md-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">Kelola User</h2>
                    <p class="text-muted mb-0">Manajemen pengguna sistem</p>
                </div>
            </div>
            
            <div class="card mb-3">
                <div class="card-body">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalUser">
                        <i class="bi bi-plus-circle"></i> Tambah User
                    </button>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Username</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Telepon</th>
                                    <th>Role</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $result = mysqli_query($conn, "SELECT * FROM users ORDER BY id ASC");
                                $no = 1;
                                while ($row = mysqli_fetch_assoc($result)):
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><strong><?php echo $row['username']; ?></strong></td>
                                    <td><?php echo $row['nama']; ?></td>
                                    <td><?php echo $row['email']; ?></td>
                                    <td><?php echo $row['telepon']; ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $row['role'] == 'admin' ? 'danger' : ($row['role'] == 'petugas' ? 'warning' : 'info'); ?>">
                                            <?php echo ucfirst($row['role']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-warning" onclick="editUser(<?php echo $row['id']; ?>, '<?php echo addslashes($row['username']); ?>', '<?php echo addslashes($row['nama']); ?>', '<?php echo addslashes($row['email']); ?>', '<?php echo addslashes($row['telepon']); ?>', '<?php echo $row['role']; ?>')">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus user <?php echo addslashes($row['nama']); ?>?')">
                                            <i class="bi bi-trash"></i>
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
</div>

<div class="modal fade" id="modalUser" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="formUser">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="userId" value="">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" id="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password <span id="passwordNote">(kosongkan jika tidak diubah)</span></label>
                        <input type="password" name="password" id="password" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama" id="nama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Telepon</label>
                        <input type="text" name="telepon" id="telepon" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role" id="role" class="form-control" required>
                            <option value="admin">Admin</option>
                            <option value="petugas">Petugas</option>
                            <option value="peminjam">Peminjam</option>
                        </select>
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
function editUser(id, username, nama, email, telepon, role) {
    document.getElementById('userId').value = id;
    document.getElementById('username').value = username;
    document.getElementById('nama').value = nama;
    document.getElementById('email').value = email;
    document.getElementById('telepon').value = telepon;
    document.getElementById('role').value = role;
    document.getElementById('password').value = '';
    document.getElementById('password').removeAttribute('required');
    document.getElementById('modalTitle').textContent = 'Edit User';
    document.getElementById('passwordNote').style.display = 'inline';
    
    var modal = new bootstrap.Modal(document.getElementById('modalUser'));
    modal.show();
}

// Reset form when adding new user
document.querySelector('[data-bs-target="#modalUser"]').addEventListener('click', function() {
    document.getElementById('formUser').reset();
    document.getElementById('userId').value = '';
    document.getElementById('password').setAttribute('required', 'required');
    document.getElementById('modalTitle').textContent = 'Tambah User';
    document.getElementById('passwordNote').style.display = 'none';
});
</script>

<?php include '../includes/footer.php'; ?>
