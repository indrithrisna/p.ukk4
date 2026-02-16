<?php
session_start();
require_once '../config/database.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user data first
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=$user_id"));

// Check if foto_profile column exists
$columns = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'foto_profile'");
$has_foto_column = mysqli_num_rows($columns) > 0;

// Handle update profile
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = clean($_POST['nama'] ?? '');
    $email = clean($_POST['email'] ?? '');
    $telepon = clean($_POST['telepon'] ?? '');
    
    // Handle foto upload only if column exists
    $foto_profile = isset($user['foto_profile']) ? $user['foto_profile'] : null;
    if ($has_foto_column && isset($_FILES['foto_profile']) && $_FILES['foto_profile']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['foto_profile']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        if (in_array(strtolower($filetype), $allowed)) {
            // Create uploads directory if not exists
            $upload_dir = '../uploads/profiles/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Delete old photo if exists
            if (isset($user['foto_profile']) && $user['foto_profile'] && file_exists('../' . $user['foto_profile'])) {
                unlink('../' . $user['foto_profile']);
            }
            
            // Generate unique filename
            $new_filename = 'profile_' . $user_id . '_' . time() . '.' . $filetype;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['foto_profile']['tmp_name'], $upload_path)) {
                $foto_profile = 'uploads/profiles/' . $new_filename;
            }
        } else {
            $error = "Format file tidak didukung. Gunakan JPG, PNG, atau GIF.";
        }
    }
    
    // Build query based on whether foto_profile column exists
    if ($has_foto_column) {
        $query = "UPDATE users SET nama='$nama', email='$email', telepon='$telepon', foto_profile='$foto_profile' WHERE id=$user_id";
    } else {
        $query = "UPDATE users SET nama='$nama', email='$email', telepon='$telepon' WHERE id=$user_id";
    }
    
    if (!empty($_POST['password_baru'])) {
        $password_lama = $_POST['password_lama'];
        $password_baru = $_POST['password_baru'];
        
        // Cek password lama
        $user_check = mysqli_fetch_assoc(mysqli_query($conn, "SELECT password FROM users WHERE id=$user_id"));
        if (password_verify($password_lama, $user_check['password'])) {
            $password_hash = password_hash($password_baru, PASSWORD_DEFAULT);
            if ($has_foto_column) {
                $query = "UPDATE users SET nama='$nama', email='$email', telepon='$telepon', foto_profile='$foto_profile', password='$password_hash' WHERE id=$user_id";
            } else {
                $query = "UPDATE users SET nama='$nama', email='$email', telepon='$telepon', password='$password_hash' WHERE id=$user_id";
            }
        } else {
            $error = "Password lama salah!";
        }
    }
    
    if (!isset($error)) {
        mysqli_query($conn, $query);
        $_SESSION['nama'] = $nama;
        if ($has_foto_column && isset($foto_profile)) {
            $_SESSION['foto_profile'] = $foto_profile;
        }
        logActivity($user_id, 'Update Profile', 'Mengubah data profile');
        $success = "Profile berhasil diperbarui!";
        // Refresh user data
        $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=$user_id"));
    }
}

// Get user data if not from POST
if (!isset($user)) {
    $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=$user_id"));
}

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
        <?php include '../includes/admin_sidebar.php'; ?>
        
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
            
            <?php if (!$has_foto_column): ?>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i> <strong>Fitur foto profile belum aktif!</strong><br>
                    Jalankan <a href="../update_foto_profile.php" class="alert-link">update_foto_profile.php</a> untuk mengaktifkan fitur ini.
                </div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-md-4">
                    <?php if ($has_foto_column): ?>
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Foto Profile</h5>
                        </div>
                        <div class="card-body text-center">
                            <?php if (isset($user['foto_profile']) && $user['foto_profile'] && file_exists('../' . $user['foto_profile'])): ?>
                                <img src="../<?php echo $user['foto_profile']; ?>" alt="Profile" class="img-fluid rounded-circle mb-3" style="width: 200px; height: 200px; object-fit: cover; border: 5px solid #4FC3F7;">
                            <?php else: ?>
                                <div class="avatar-placeholder mb-3" style="width: 200px; height: 200px; margin: 0 auto; background: var(--blue-gradient); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 5rem; color: white; border: 5px solid #4FC3F7;">
                                    <i class="bi bi-person-circle"></i>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" enctype="multipart/form-data" id="formFoto">
                                <div class="mb-3">
                                    <input type="file" name="foto_profile" id="foto_profile" class="form-control" accept="image/*" onchange="previewImage(this)">
                                    <small class="text-muted">Format: JPG, PNG, GIF (Max 2MB)</small>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="bi bi-upload"></i> Upload Foto
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="card <?php echo $has_foto_column ? 'mt-3' : ''; ?>">
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
                
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Informasi Profile</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label class="form-label">Username</label>
                                    <input type="text" class="form-control" value="<?php echo $user['username']; ?>" disabled>
                                    <small class="text-muted">Username tidak dapat diubah</small>
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
                                    <small class="text-muted">Kosongkan jika tidak ingin mengubah password</small>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Simpan Perubahan
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            // You can add preview functionality here if needed
            console.log('Image selected:', input.files[0].name);
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php include '../includes/footer.php'; ?>
