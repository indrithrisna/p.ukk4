<?php
require_once __DIR__ . '/../controllers/Controller.php';
require_once __DIR__ . '/../models/AlatModel.php';

class AlatController extends Controller {
    private $model;

    public function __construct($conn) {
        $this->model = new AlatModel($conn);
    }

    public function index($showDeleted = false) {
        return $this->model->getAll($showDeleted);
    }

    public function save($postData, $role = ['admin', 'petugas']) {
        $this->requireRole($role);
        $id = (int)($postData['id'] ?? 0);

        if ($id > 0) {
            $this->model->update($id, $postData);
            logActivity($_SESSION['user_id'], 'Update Alat', "Mengubah alat: " . $postData['nama_alat']);
        } else {
            $this->model->create($postData);
            logActivity($_SESSION['user_id'], 'Tambah Alat', "Menambah alat: " . $postData['nama_alat']);
        }
        $this->redirect('alat.php');
    }

    public function delete($id, $role = ['admin', 'petugas']) {
        $this->requireRole($role);
        $alat = $this->model->getById($id);
        $this->model->softDelete($id);
        logActivity($_SESSION['user_id'], 'Hapus Alat', "Menghapus alat: " . $alat['nama_alat']);
        $this->redirect('alat.php');
    }

    public function restore($id, $role = 'admin') {
        $this->requireRole($role);
        $alat = $this->model->getById($id);
        $this->model->restore($id);
        logActivity($_SESSION['user_id'], 'Restore Alat', "Mengembalikan alat: " . $alat['nama_alat']);
        $this->redirect('alat.php?show=deleted');
    }

    public function permanentDelete($id, $role = 'admin') {
        $this->requireRole($role);
        $alat = $this->model->getById($id);
        $this->model->permanentDelete($id);
        logActivity($_SESSION['user_id'], 'Hapus Permanen Alat', "Hapus permanen: " . $alat['nama_alat']);
        $this->redirect('alat.php?show=deleted');
    }

    public function getAvailable() {
        return $this->model->getAvailable();
    }
}
?>
