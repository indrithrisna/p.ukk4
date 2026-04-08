<?php
require_once __DIR__ . '/Model.php';

class UserModel extends Model {

    public function getAll() {
        return $this->fetchAll("SELECT * FROM users ORDER BY id ASC");
    }

    public function getById($id) {
        return $this->fetchOne("SELECT * FROM users WHERE id = " . (int)$id);
    }

    public function getByUsername($username) {
        $username = $this->escape($username);
        return $this->fetchOne("SELECT * FROM users WHERE username = '$username'");
    }

    public function create($data) {
        $username = $this->escape($data['username']);
        $password = password_hash($data['password'], PASSWORD_DEFAULT);
        $nama     = $this->escape($data['nama']);
        $email    = $this->escape($data['email']);
        $telepon  = $this->escape($data['telepon']);
        $role     = $this->escape($data['role']);
        return $this->query("INSERT INTO users (username, password, nama, email, telepon, role)
                             VALUES ('$username','$password','$nama','$email','$telepon','$role')");
    }

    public function update($id, $data) {
        $id       = (int)$id;
        $username = $this->escape($data['username']);
        $nama     = $this->escape($data['nama']);
        $email    = $this->escape($data['email']);
        $telepon  = $this->escape($data['telepon']);
        $role     = $this->escape($data['role']);

        if (!empty($data['password'])) {
            $password = password_hash($data['password'], PASSWORD_DEFAULT);
            return $this->query("UPDATE users SET username='$username', password='$password',
                                 nama='$nama', email='$email', telepon='$telepon', role='$role'
                                 WHERE id=$id");
        }
        return $this->query("UPDATE users SET username='$username', nama='$nama',
                             email='$email', telepon='$telepon', role='$role' WHERE id=$id");
    }

    public function delete($id) {
        $id = (int)$id;
        // Hapus log aktivitas dulu sebelum hapus user (foreign key constraint)
        $this->query("DELETE FROM log_aktivitas WHERE user_id = $id");
        // Hapus peminjaman terkait jika ada
        $this->query("DELETE FROM detail_peminjaman WHERE peminjaman_id IN (SELECT id FROM peminjaman WHERE peminjam_id = $id)");
        $this->query("DELETE FROM peminjaman WHERE peminjam_id = $id");
        return $this->query("DELETE FROM users WHERE id = $id");
    }

    public function updateProfile($id, $data) {
        $id    = (int)$id;
        $nama  = $this->escape($data['nama']);
        $email = $this->escape($data['email']);
        $telepon = $this->escape($data['telepon']);
        $sql = "UPDATE users SET nama='$nama', email='$email', telepon='$telepon' WHERE id=$id";
        if (!empty($data['foto_profile'])) {
            $foto = $this->escape($data['foto_profile']);
            $sql = "UPDATE users SET nama='$nama', email='$email', telepon='$telepon', foto_profile='$foto' WHERE id=$id";
        }
        return $this->query($sql);
    }

    public function countByRole($role) {
        $role = $this->escape($role);
        $row = $this->fetchOne("SELECT COUNT(*) as total FROM users WHERE role='$role'");
        return $row['total'];
    }
}
?>
