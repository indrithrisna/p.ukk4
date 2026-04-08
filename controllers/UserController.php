<?php
require_once __DIR__ . '/../controllers/Controller.php';
require_once __DIR__ . '/../models/UserModel.php';

class UserController extends Controller {
    private $model;

    public function __construct($conn) {
        $this->model = new UserModel($conn);
    }

    // Tampilkan semua user
    public function index() {
        $this->requireRole('admin');
        return $this->model->getAll();
    }

    // Tambah atau edit user
    public function save($postData) {
        $this->requireRole('admin');
        $id = (int)($postData['id'] ?? 0);

        if ($id > 0) {
            $this->model->update($id, $postData);
            logActivity($_SESSION['user_id'], 'Update User', "Mengubah data user: " . $postData['nama']);
        } else {
            $this->model->create($postData);
            logActivity($_SESSION['user_id'], 'Tambah User', "Menambah user: " . $postData['nama'] . " (" . $postData['role'] . ")");
        }
        $this->redirect('users.php');
    }

    // Hapus user
    public function delete($id) {
        $this->requireRole('admin');
        $user = $this->model->getById($id);
        $this->model->delete($id);
        logActivity($_SESSION['user_id'], 'Hapus User', "Menghapus user: " . $user['nama']);
        $this->redirect('users.php');
    }

    // Login
    public function login($username, $password) {
        $user = $this->model->getByUsername($username);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['role'] = $user['role'];
            return $user;
        }
        return false;
    }

    // Update profile
    public function updateProfile($id, $postData, $fotoPath = null) {
        if ($fotoPath) $postData['foto_profile'] = $fotoPath;
        $this->model->updateProfile($id, $postData);
        $_SESSION['nama'] = $postData['nama'];
        logActivity($_SESSION['user_id'], 'Update Profile', "Mengubah data profile");
    }
}
?>
