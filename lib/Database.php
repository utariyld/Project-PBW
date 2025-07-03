<?php
// lib/Database.php

class Database {
    private $host;
    private $username;
    private $password;
    private $db_name;
    private $port;
    public $conn;

    public function __construct() {
        $database = new Database(); // Buat instance baru dari kelas Database
        $this->db = $database->getConnection(); // Dapatkan objek PDO dari instance tersebut
    }

        $this->host = $database_config['host'];
        $this->username = $database_config['username'];
        $this->password = $database_config['password'];
        $this->db_name = $database_config['database'];
        $this->port = $database_config['port'];
        
        $this->conn = null;

        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset=utf8mb4";
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); // Fetch as associative array
        } catch(PDOException $exception) {
            error_log("Database connection error: " . $exception->getMessage());
            die("Koneksi database gagal: " . $exception->getMessage());
        }
    }

    public function getConnection() {
        return $this->conn;
    }
}
?>