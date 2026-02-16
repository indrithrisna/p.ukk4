<div class="col-md-2 sidebar p-0">
    <div class="p-3">
        <h5 class="text-white">Menu Petugas</h5>
    </div>
    
    <!-- User Card -->
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
    
    <!-- Main Menu -->
    <div class="sidebar-menu-group">
        <div class="sidebar-menu-label">Main Menu</div>
        <a href="dashboard.php" <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'class="active"' : ''; ?>>
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="alat.php" <?php echo basename($_SERVER['PHP_SELF']) == 'alat.php' ? 'class="active"' : ''; ?>>
            <i class="bi bi-box-seam"></i> Kelola Alat
        </a>
        <a href="kategori.php" <?php echo basename($_SERVER['PHP_SELF']) == 'kategori.php' ? 'class="active"' : ''; ?>>
            <i class="bi bi-tags-fill"></i> Kategori
        </a>
    </div>
    
    <div class="sidebar-divider"></div>
    
    <!-- Transaction Menu -->
    <div class="sidebar-menu-group">
        <div class="sidebar-menu-label">Transaksi</div>
        <a href="peminjaman.php" <?php echo basename($_SERVER['PHP_SELF']) == 'peminjaman.php' ? 'class="active"' : ''; ?>>
            <i class="bi bi-clipboard-check-fill"></i> Peminjaman
        </a>
        <a href="pemantau_pengembalian.php" <?php echo basename($_SERVER['PHP_SELF']) == 'pemantau_pengembalian.php' ? 'class="active"' : ''; ?>>
            <i class="bi bi-arrow-return-left"></i> Pemantau Pengembalian
        </a>
        <a href="pengaturan_denda.php" <?php echo basename($_SERVER['PHP_SELF']) == 'pengaturan_denda.php' ? 'class="active"' : ''; ?>>
            <i class="bi bi-cash-coin"></i> Pengaturan Denda
        </a>
    </div>
    
    <div class="sidebar-divider"></div>
    
    <!-- Report Menu -->
    <div class="sidebar-menu-group">
        <div class="sidebar-menu-label">Laporan</div>
        <a href="laporan.php" <?php echo basename($_SERVER['PHP_SELF']) == 'laporan.php' ? 'class="active"' : ''; ?>>
            <i class="bi bi-file-earmark-bar-graph"></i> Laporan
        </a>
    </div>
    
    <div class="sidebar-divider"></div>
    
    <!-- Account Menu -->
    <div class="sidebar-menu-group">
        <div class="sidebar-menu-label">Akun</div>
        <a href="profile.php" <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'class="active"' : ''; ?>>
            <i class="bi bi-person-circle"></i> Profile
        </a>
    </div>
</div>

