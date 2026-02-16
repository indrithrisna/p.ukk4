<?php
// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'event_rental');

// Koneksi Database
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Set charset
mysqli_set_charset($conn, "utf8");

// Auto-create tables jika belum ada
function autoCreateTables() {
    global $conn;
    
    // Cek tabel log_aktivitas
    $check = @mysqli_query($conn, "SHOW TABLES LIKE 'log_aktivitas'");
    if (mysqli_num_rows($check) == 0) {
        $query = "CREATE TABLE log_aktivitas (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            aktivitas TEXT NOT NULL,
            keterangan TEXT,
            ip_address VARCHAR(45),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )";
        @mysqli_query($conn, $query);
    }
}

// Jalankan auto-create saat pertama kali load
autoCreateTables();

// Fungsi untuk mencegah SQL Injection
function clean($data) {
    global $conn;
    if ($data === null || $data === '') {
        return '';
    }
    return mysqli_real_escape_string($conn, htmlspecialchars(trim($data)));
}

// Fungsi untuk cek login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Fungsi untuk cek role
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

// Fungsi untuk log aktivitas
function logActivity($user_id, $aktivitas, $keterangan = '') {
    global $conn;
    
    // Validasi user_id
    if (empty($user_id) || !is_numeric($user_id)) {
        return;
    }
    
    // Cek apakah user_id valid
    $check = @mysqli_query($conn, "SELECT id FROM users WHERE id = " . (int)$user_id);
    if (!$check || mysqli_num_rows($check) == 0) {
        return; // User tidak ditemukan, skip log
    }
    
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
    $aktivitas = mysqli_real_escape_string($conn, htmlspecialchars(trim($aktivitas)));
    $keterangan = mysqli_real_escape_string($conn, htmlspecialchars(trim($keterangan)));
    
    $query = "INSERT INTO log_aktivitas (user_id, aktivitas, keterangan, ip_address) 
              VALUES (" . (int)$user_id . ", '$aktivitas', '$keterangan', '$ip')";
    @mysqli_query($conn, $query);
}
?>
