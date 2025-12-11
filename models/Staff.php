<?php
// models/Staff.php

require_once __DIR__ . '/../config/Database.php';

class Staff {
    private $conn;
    private $table = "users"; // Staff records are stored in the users table

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    /**
     * Retrieves all users who are marked as 'driver' or 'staff'.
     * Excludes 'admin' and 'passenger' roles.
     * @return array
     */
    public function readAll() {
        $query = "SELECT 
                    user_id, 
                    name, 
                    email, 
                    phone, 
                    user_type, 
                    employee_id,
                    license_number,
                    created_at
                  FROM " . $this->table . " 
                  WHERE user_type IN ('driver', 'staff')
                  ORDER BY user_type, name";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // error_log("Staff Read All Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Retrieves a single staff record by ID.
     * @param int $userId
     * @return array|false Staff record or false if not found.
     */
    public function readOne($userId) {
        $query = "SELECT 
                    user_id, name, email, phone, user_type, employee_id, license_number
                  FROM " . $this->table . " 
                  WHERE user_id = :id AND user_type IN ('driver', 'staff') LIMIT 1";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Creates a new staff record (user).
     * @param array $data
     * @return bool True on success, false on failure.
     */
    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (name, email, phone, password, user_type, employee_id, license_number) 
                  VALUES (:name, :email, :phone, :password, :user_type, :employee_id, :license_number)";
        
        try {
            $stmt = $this->conn->prepare($query);
            
            // Hash password before saving
            $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Clean and bind parameters
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':user_type', $data['user_type']);
            $stmt->bindParam(':employee_id', $data['employee_id']);
            $stmt->bindParam(':license_number', $data['license_number']);

            return $stmt->execute();
        } catch (PDOException $e) {
            // error_log("Staff Create Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Updates an existing staff record.
     * @param array $data
     * @return bool True on success, false on failure.
     */
    public function update($data) {
        $query = "UPDATE " . $this->table . " 
                  SET 
                    name = :name, 
                    email = :email, 
                    phone = :phone, 
                    user_type = :user_type, 
                    employee_id = :employee_id,
                    license_number = :license_number
                  WHERE user_id = :user_id";
        
        try {
            $stmt = $this->conn->prepare($query);
            
            // Clean and bind parameters
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':user_type', $data['user_type']);
            $stmt->bindParam(':employee_id', $data['employee_id']);
            $stmt->bindParam(':license_number', $data['license_number']);
            $stmt->bindParam(':user_id', $data['user_id'], PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            // error_log("Staff Update Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Deletes a staff record.
     * @param int $userId
     * @return bool True on success, false on failure.
     */
    public function delete($userId) {
        $query = "DELETE FROM " . $this->table . " WHERE user_id = :id AND user_type IN ('driver', 'staff')";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            // Added check in WHERE clause to prevent accidental deletion of Admin users.
            return $stmt->execute();
        } catch (PDOException $e) {
            // error_log("Staff Delete Error: " . $e->getMessage());
            return false;
        }
    }
}
?>