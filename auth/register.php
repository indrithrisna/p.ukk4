<?php
session_start();
require_once '../config/database.php';

if (isLoggedIn()) {
    header("Location: ../index.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $nama = clean($_POST['nama']);
    $email = clean($_POST['email']);
    $telepon = clean($_POST['telepon']);
    
    // Cek username sudah ada
    $check = mysqli_query($conn, "SELECT id FROM users WHERE username = '$username'");
    if (mysqli_num_rows($check) > 0) {
        $error = 'Username sudah digunakan!';
    } else {
        $query = "INSERT INTO users (username, password, nama, email, telepon, role) 
                  VALUES ('$username', '$password', '$nama', '$email', '$telepon', 'peminjam')";
        
        if (mysqli_query($conn, $query)) {
            $success = 'Registrasi berhasil! Silakan login.';
        } else {
            $error = 'Registrasi gagal: ' . mysqli_error($conn);
        }
    }
}

$page_title = "Register";
include '../includes/header.php';
?>

<div class="container mt-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg border-0" style="border-radius: 20px; overflow: hidden;">
                <div class="card-header text-white text-center py-4" style="background: linear-gradient(135deg, #66BB6A 0%, #43A047 100%); border: none;">
                    <i class="bi bi-person-plus-fill" style="font-size: 3rem;"></i>
                    <h3 class="mb-0 mt-2 fw-bold">Daftar Akun Baru</h3>
                    <p class="mb-0 opacity-75">Sistem Peminjaman Alat Event</p>
                </div>
                <div class="card-body p-4">
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle"></i> <?php echo $success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-person text-success"></i> Username
                                </label>
                                <input type="text" name="username" class="form-control" placeholder="Username" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-lock text-success"></i> Password
                                </label>
                                <input type="password" name="password" class="form-control" placeholder="Password" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-person-badge text-success"></i> Nama Lengkap
                            </label>
                            <input type="text" name="nama" class="form-control" placeholder="Nama lengkap" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-envelope text-success"></i> Email
                            </label>
                            <input type="email" name="email" class="form-control" placeholder="email@example.com" required>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="bi bi-telephone text-success"></i> Telepon
                            </label>
                            <input type="text" name="telepon" class="form-control" placeholder="08xxxxxxxxxx" required>
                        </div>
                        
                        <button type="submit" class="btn btn-success btn-lg w-100 fw-bold" style="border-radius: 10px;">
                            <i class="bi bi-person-plus"></i> Daftar Sekarang
                        </button>
                    </form>
                    
                    <hr class="my-4">
                    
                    <div class="text-center">
                        <p class="text-muted mb-2">Sudah punya akun?</p>
                        <a href="login.php" class="btn btn-outline-success">
                            <i class="bi bi-box-arrow-in-right"></i> Login di sini
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
