<?php
session_start();
require_once '../config/database.php';

if (isLoggedIn()) {
    header("Location: ../index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean($_POST['username']);
    $password = $_POST['password'];
    
    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['foto_profile'] = $user['foto_profile'];
            
            // Log aktivitas login
            logActivity($user['id'], 'Login', 'User berhasil login ke sistem');
            
            // Redirect berdasarkan role
            if ($user['role'] == 'admin') {
                header("Location: ../admin/dashboard.php");
            } elseif ($user['role'] == 'petugas') {
                header("Location: ../petugas/dashboard.php");
            } else {
                header("Location: ../peminjam/dashboard.php");
            }
            exit();
        } else {
            $error = 'Password salah!';
        }
    } else {
        $error = 'Username tidak ditemukan!';
    }
}

$page_title = "Login";
include '../includes/header.php';
?>

<div class="container mt-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-lg border-0" style="border-radius: 20px; overflow: hidden;">
                <div class="card-header text-white text-center py-4" style="background: var(--blue-gradient); border: none;">
                    <i class="bi bi-box-arrow-in-right" style="font-size: 3rem;"></i>
                    <h3 class="mb-0 mt-2 fw-bold">Selamat Datang</h3>
                    <p class="mb-0 opacity-75">Sistem Peminjaman Alat Event</p>
                </div>
                <div class="card-body p-4">
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="bi bi-person-fill text-primary"></i> Username
                            </label>
                            <input type="text" name="username" class="form-control form-control-lg" placeholder="Masukkan username" required autofocus>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="bi bi-lock-fill text-primary"></i> Password
                            </label>
                            <input type="password" name="password" class="form-control form-control-lg" placeholder="Masukkan password" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold" style="border-radius: 10px;">
                            <i class="bi bi-box-arrow-in-right"></i> Login
                        </button>
                    </form>
                    
                    <hr class="my-4">
                    
                    <div class="text-center">
                        <p class="text-muted mb-2">Belum punya akun?</p>
                        <a href="register.php" class="btn btn-outline-primary">
                            <i class="bi bi-person-plus"></i> Daftar Sekarang
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <small class="text-muted">
                    <i class="bi bi-shield-check"></i> Sistem Aman & Terpercaya
                </small>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
