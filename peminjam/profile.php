<?php
session_start();
require_once '../config/database.php';

if (!isLoggedIn() || !hasRole('peminjam')) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle update profile
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = clean($_POST['nama']);
    $email = clean($_POST['email']);
    $telepon = clean($_POST['telepon']);
    
    $query = "UPDATE users SET nama='$nama', email='$email', telepon='$telepon' WHERE id=$user_id";
    
    if (!empty($_POST['password_baru'])) {
        $password_lama = $_POST['password_lama'];
        $password_baru = $_POST['password_baru'];
        
        $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT password FROM users WHERE id=$user_id"));
        if (password_verify($password_lama, $user['password'])) {
            $password_hash = password_hash($password_baru, PASSWORD_DEFAULT);
            $query = "UPDATE users SET nama='$nama', email='$email', telepon='$telepon', password='$password_hash' WHERE id=$user_id";
        } else {
            $error = "Password lama salah!";
        }
    }
    
    if (!isset($error)) {
        mysqli_query($conn, $query);
        $_SESSION['nama'] = $nama;
        logActivity($user_id, 'Update Profile', 'Mengubah data profile');
        $success = "Profile berhasil diperbarui!";
    }
}

$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=$user_id"));

// Jika user tidak ditemukan, redirect ke logout
if (!$user) {
    header("Location: ../auth/logout.php");
    exit();
}

$page_title = "Profile Saya";
include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/peminjam_sidebar.php'; ?>
        <div class="col-md-10 p-4">
            <div class="alert alert-info">
                <h4><i class="bi bi-hand-wave"></i> Selamat datang, <?php echo $_SESSION['nama']; ?>!</h4>
                <p class="mb-0">Anda login sebagai <strong><?php echo ucfirst($_SESSION['role']); ?></strong></p>
            </div>
            
            <h2>Profile Saya</h2>
            <hr>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Informasi Profile</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Username</label>
                                    <input type="text" class="form-control" value="<?php echo $user['username']; ?>" disabled>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Nama Lengkap</label>
                                    <input type="text" name="nama" class="form-control" value="<?php echo $user['nama']; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" value="<?php echo $user['email']; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Telepon</label>
                                    <input type="text" name="telepon" class="form-control" value="<?php echo $user['telepon']; ?>">
                                </div>
                                
                                <hr>
                                <h6>Ubah Password (Opsional)</h6>
                                
                                <div class="mb-3">
                                    <label class="form-label">Password Lama</label>
                                    <input type="password" name="password_lama" class="form-control">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Password Baru</label>
                                    <input type="password" name="password_baru" class="form-control">
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Simpan Perubahan
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0">Informasi Akun</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Role</strong></td>
                                    <td><?php echo ucfirst($user['role']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Terdaftar</strong></td>
                                    <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>


