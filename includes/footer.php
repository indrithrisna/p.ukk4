    <footer class="bg-light text-center py-3 mt-5">
        <p class="mb-0">&copy; 2026 Sistem Peminjaman Alat (12 RPL 3). All rights reserved.</p>
    </footer>
    
    <!-- jQuery harus di-load dulu sebelum Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap Bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="<?php echo $base ?? '../'; ?>assets/js/script.js"></script>
    
    <!-- Fix untuk button close alert -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto close alerts after 5 seconds
        if (window.bootstrap && bootstrap.Alert) {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        }
        
        // Ensure all close buttons work
        const closeButtons = document.querySelectorAll('[data-bs-dismiss]');
        closeButtons.forEach(function(btn) {
            btn.addEventListener('click', function() {
                const target = this.getAttribute('data-bs-dismiss');
                if (target === 'alert') {
                    const alert = this.closest('.alert');
                    if (alert) {
                        if (window.bootstrap && bootstrap.Alert) {
                            const bsAlert = new bootstrap.Alert(alert);
                            bsAlert.close();
                        } else {
                            alert.remove();
                        }
                    }
                } else if (target === 'modal') {
                    const modal = this.closest('.modal');
                    if (modal) {
                        if (window.bootstrap && bootstrap.Modal) {
                            const bsModal = bootstrap.Modal.getInstance(modal);
                            if (bsModal) {
                                bsModal.hide();
                            }
                        } else {
                            modal.classList.remove('show');
                            modal.style.display = 'none';
                            modal.setAttribute('aria-hidden', 'true');
                        }
                    }
                }
            });
        });
    });
    </script>
</body>
</html>
