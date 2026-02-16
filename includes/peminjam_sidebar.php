<div class="col-md-2 sidebar p-0">
    <div class="p-3">
        <h5 class="text-white">Menu Peminjam</h5>
    </div>

    <div class="sidebar-user-card">
        <div class="user-avatar">
            <?php if (isset($_SESSION['foto_profile']) && $_SESSION['foto_profile'] && file_exists('../' . $_SESSION['foto_profile'])): ?>
                <img src="../<?php echo $_SESSION['foto_profile']; ?>" alt="Profile" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
            <?php else: ?>
                <i class="bi bi-person-circle"></i>
            <?php endif; ?>
        </div>
        <div class="user-name"><?php echo $_SESSION['nama']; ?></div>
        <div class="user-role"><?php echo ucfirst($_SESSION['role']); ?></div>
    </div>

    <div class="sidebar-divider"></div>

    <div class="sidebar-menu-group">
        <div class="sidebar-menu-label">Main Menu</div>
        <a href="dashboard.php" <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'class="active"' : ''; ?>>
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="alat.php" <?php echo basename($_SERVER['PHP_SELF']) == 'alat.php' ? 'class="active"' : ''; ?>>
            <i class="bi bi-box-seam"></i> Daftar Alat
        </a>
    </div>

    <div class="sidebar-divider"></div>

    <div class="sidebar-menu-group">
        <div class="sidebar-menu-label">Peminjaman</div>
        <a href="peminjaman.php" <?php echo basename($_SERVER['PHP_SELF']) == 'peminjaman.php' ? 'class="active"' : ''; ?>>
            <i class="bi bi-clipboard-check-fill"></i> Peminjaman Saya
        </a>
        <a href="riwayat.php" <?php echo basename($_SERVER['PHP_SELF']) == 'riwayat.php' ? 'class="active"' : ''; ?>>
            <i class="bi bi-clock-history"></i> Riwayat
        </a>
    </div>

    <div class="sidebar-divider"></div>

    <div class="sidebar-menu-group">
        <div class="sidebar-menu-label">Akun</div>
        <a href="profile.php" <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'class="active"' : ''; ?>>
            <i class="bi bi-person-circle"></i> Profile
        </a>
    </div>
</div>
