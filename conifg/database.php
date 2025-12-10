<?php

class Database {
    // Database credentials (adjust these if you changed XAMPP defaults)
    private $host = "localhost";
    private $db_name = "bus_booking_db"; // The name we used in the SQL setup
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
            
            // Set default fetch mode to associative array
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            // Optional: for persistent connections, though generally not recommended for web apps
            // $this->conn->setAttribute(PDO::ATTR_PERSISTENT, true);

        } catch(PDOException $exception) {
            // In a production environment, you would log this error, not print it to the screen
            echo "Connection Error: " . $exception->getMessage();
            die(); // Halt execution on connection failure
        }

        return $this->conn;
    }
}

// How to test the connection (optional: remove this test block later)
/*
$db = new Database();
if ($db->connect()) {
    echo "Database connection successful!";
} else {
    echo "Database connection failed.";
}
*/
?>