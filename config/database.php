<?php
class Database {
    private $host = "localhost";
    private $db_name = "db_villagehub";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4",
                $this->username, $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            header("Content-Type: application/json");
            echo json_encode(["success"=>false,"message"=>"DB Error: ".$e->getMessage()]);
            exit;
        }
        return $this->conn;
    }
}