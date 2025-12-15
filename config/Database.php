<?php
// config/Database.php

class Database {
    private $host = 'localhost';
    private $db_name = 'bus_booking_db'; // Ensure this matches your database name
    private $username = 'root';          // Ensure this matches your database username
    private $password = '';              // Ensure this matches your database password
    private $conn;

    /**
     * Get the database connection
     * @return PDO The PDO connection object
     */
    public function connect() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Set character set to UTF-8
            $this->conn->exec("set names utf8"); 
        } catch(PDOException $exception) {
            // Log the error and display a user-friendly message
            error_log("Database connection error: " . $exception->getMessage());
            die("Database connection failed. Please check your config/Database.php file.");
        }
        return $this->conn;
    }
}