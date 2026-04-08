<?php
require_once __DIR__ . '/../controllers/Controller.php';
require_once __DIR__ . '/../models/KategoriModel.php';

class KategoriController extends Controller {
    private $model;

    public function __construct($conn) {
        $this->model = new KategoriModel($conn);
    }

    public function index() {
        return $this->model->getAll();
    }

    public function save($postData, $role = ['admin', 'petugas']) {
        $this->requireRole($role);
        $id = (int)($postData['id'] ?? 0);

        if ($id > 0) {
            $this->model->update($id, $postData);
            logActivity($_SESSION['user_id'], 'Update Kategori', "Mengubah kategori: " . $postData['nama_kategori']);
        } else {
            $this->model->create($postData);
            logActivity($_SESSION['user_id'], 'Tambah Kategori', "Menambah kategori: " . $postData['nama_kategori']);
        }
        $this->redirect('kategori.php');
    }

    public function delete($id, $role = ['admin', 'petugas']) {
        $this->requireRole($role);
        $kat = $this->model->getById($id);
        $this->model->delete($id);
        logActivity($_SESSION['user_id'], 'Hapus Kategori', "Menghapus kategori: " . $kat['nama_kategori']);
        $this->redirect('kategori.php');
    }
}
?>
