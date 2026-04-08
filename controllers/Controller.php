<?php
// Base Controller
class Controller {

    protected function redirect($url) {
        header("Location: $url");
        exit();
    }

    protected function isPost() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function isGet($key) {
        return isset($_GET[$key]);
    }

    protected function post($key, $default = '') {
        return isset($_POST[$key]) ? $_POST[$key] : $default;
    }

    protected function get($key, $default = '') {
        return isset($_GET[$key]) ? $_GET[$key] : $default;
    }

    protected function requireLogin() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('../auth/login.php');
        }
    }

    protected function requireRole($role) {
        $this->requireLogin();
        // Support multi-role: bisa string atau array
        $roles = is_array($role) ? $role : [$role];
        if (!in_array($_SESSION['role'], $roles)) {
            $this->redirect('../auth/login.php');
        }
    }
}
?>
