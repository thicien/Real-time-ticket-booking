<?php

class Database {
    // Database credentials (adjust these if you changed XAMPP defaults)
    private $host = "localhost";
    private $db_name = "bus_booking_db"; // Ensure this is your actual database name
    private $username = "root";         // Default XAMPP username
    private $password = "";             // Default XAMPP password
    private $conn;

    /**
     * Get the database connection
     *
     * @return PDO|null
     */
    public function connect() {
        $this->conn = null;

        try {
            // Data Source Name (DSN) for MySQL
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4";
            
            // Create a new PDO connection instance
            $this->conn = new PDO($dsn, $this->username, $this->password);
            
            // Set error mode to exception for better error handling
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        } catch(PDOException $exception) {
            // Handle connection failure
            echo "Connection Error: " . $exception->getMessage();
            die();
        }

        return $this->conn;
    }
}
?>