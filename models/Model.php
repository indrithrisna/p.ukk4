<?php
// Base Model - semua model extends class ini
class Model {
    protected $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    protected function query($sql) {
        return mysqli_query($this->conn, $sql);
    }

    protected function fetchAll($sql) {
        $result = $this->query($sql);
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        return $data;
    }

    protected function fetchOne($sql) {
        $result = $this->query($sql);
        return mysqli_fetch_assoc($result);
    }

    protected function escape($value) {
        return mysqli_real_escape_string($this->conn, htmlspecialchars(trim($value)));
    }
}
?>
