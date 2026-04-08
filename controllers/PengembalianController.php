<?php
require_once __DIR__ . '/../controllers/Controller.php';
require_once __DIR__ . '/../models/PengembalianModel.php';
require_once __DIR__ . '/../models/AlatModel.php';

class PengembalianController extends Controller {
    private $model;
    private $alatModel;

    public function __construct($conn) {
        $this->model     = new PengembalianModel($conn);
        $this->alatModel = new AlatModel($conn);
    }

    public function index() {
        return $this->model->getDaftarDipinjam();
    }

    public function proses($id, $postData, $role = 'petugas') {
        $this->requireRole($role);
        $this->model->proses($id, $postData, $this->alatModel);
        logActivity($_SESSION['user_id'], 'Pengembalian Alat', "Memproses pengembalian peminjaman ID: $id");
        $this->redirect('pengembalian.php');
    }

    // Pengembalian cepat tanpa form (selesai langsung)
    public function selesai($id, $role = 'admin') {
        $this->requireRole($role);
        $this->model->proses($id, ['kondisi_pengembalian' => 'baik', 'denda' => 0, 'catatan_pengembalian' => ''], $this->alatModel);
        logActivity($_SESSION['user_id'], 'Pengembalian Alat', "Menyelesaikan peminjaman ID: $id");
        $this->redirect('pengembalian.php');
    }
}
?>
