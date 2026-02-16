<?php
session_start();
require_once '../config/database.php';

if (!isLoggedIn() || !hasRole('petugas')) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle update profile
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $csrf)) {
        $error = "Permintaan tidak valid. Silakan coba lagi.";
    } else {
        $nama = trim($_POST['nama'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telepon = trim($_POST['telepon'] ?? '');

        if ($nama === '' || $email === '') {
            $error = "Nama dan email wajib diisi.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Format email tidak valid.";
        } elseif ($telepon !== '' && !preg_match('/^[0-9+\\-\\s]{6,20}$/', $telepon)) {
            $error = "Format telepon tidak valid.";
        } else {
            $password_baru = $_POST['password_baru'] ?? '';
            $password_lama = $_POST['password_lama'] ?? '';

            if ($password_baru !== '') {
                if (strlen($password_baru) < 8) {
                    $error = "Password baru minimal 8 karakter.";
                } elseif ($password_lama === '') {
                    $error = "Password lama wajib diisi.";
                } else {
                    $stmt = mysqli_prepare($conn, "SELECT password FROM users WHERE id = ?");
                    mysqli_stmt_bind_param($stmt, "i", $user_id);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    $user_row = $result ? mysqli_fetch_assoc($result) : null;
                    mysqli_stmt_close($stmt);

                    if (!$user_row || !password_verify($password_lama, $user_row['password'])) {
                        $error = "Password lama salah!";
                    } else {
                        $password_hash = password_hash($password_baru, PASSWORD_DEFAULT);
                        $stmt = mysqli_prepare($conn, "UPDATE users SET nama = ?, email = ?, telepon = ?, password = ? WHERE id = ?");
                        mysqli_stmt_bind_param($stmt, "ssssi", $nama, $email, $telepon, $password_hash, $user_id);
                        if (mysqli_stmt_execute($stmt)) {
                            $_SESSION['nama'] = $nama;
                            logActivity($user_id, 'Update Profile', 'Mengubah data profile');
                            $success = "Profile berhasil diperbarui!";
                        } else {
                            $error = "Gagal menyimpan perubahan.";
                        }
                        mysqli_stmt_close($stmt);
                    }
                }
            } else {
                $stmt = mysqli_prepare($conn, "UPDATE users SET nama = ?, email = ?, telepon = ? WHERE id = ?");
                mysqli_stmt_bind_param($stmt, "sssi", $nama, $email, $telepon, $user_id);
                if (mysqli_stmt_execute($stmt)) {
                    $_SESSION['nama'] = $nama;
                    logActivity($user_id, 'Update Profile', 'Mengubah data profile');
                    $success = "Profile berhasil diperbarui!";
                } else {
                    $error = "Gagal menyimpan perubahan.";
                }
                mysqli_stmt_close($stmt);
            }
        }
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
        <?php include '../includes/petugas_sidebar.php'; ?>
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
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
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


