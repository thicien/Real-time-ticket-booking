<?php
// config/Database.php

class Database {
    // Database connection parameters
    private $host = 'localhost';
    private $db_name = 'bus_booking_db'; // Ensure this matches your DB name
    private $username = 'root'; // Or your specific DB username
    private $password = ''; // Or your specific DB password
    private $conn;

    /**
     * Database connection method.
     * @return PDO|null The PDO connection object or null on failure.
     */
    public function connect() {
        $this->conn = null;

        try {
            // Construct the DSN (Data Source Name)
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4";
            
            // Create a new PDO instance
            $this->conn = new PDO($dsn, $this->username, $this->password);
            
            // Set error mode to exception for better error handling
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Set default fetch mode
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            // Log the error and display a generic message
            error_log("Connection Error: " . $e->getMessage());
            die("Database connection error. Please check server logs.");
        }

        return $this->conn;
    }
}
?>