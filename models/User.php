<?php

require_once __DIR__ . '/../config/Database.php'; 

class User {
    private $conn;
    private $users_table = "users";
    private $admins_table = "admins";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect(); 
    }

    public function findUserByEmail($email) {
        $query = "SELECT user_id, email, password_hash, first_name, last_name, phone_number
                     FROM " . $this->users_table . "
                     WHERE email = :email
                     LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $email = htmlspecialchars(strip_tags($email));
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        
        // Fetch the result
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Creates a new user (passenger) record in the database.
     *
     * @param array $data Contains first_name, last_name, email, phone_number, and password_hash
     * @return bool True on successful insertion, false otherwise.
     */
    public function registerUser($data) { // <-- CORRECT METHOD NAME!
        
        $query = "INSERT INTO " . $this->users_table . " 
                     (first_name, last_name, email, phone_number, password_hash)
                     VALUES (:first_name, :last_name, :email, :phone_number, :password_hash)";

        $stmt = $this->conn->prepare($query);
        $firstName = htmlspecialchars(strip_tags($data['first_name']));
        $lastName = htmlspecialchars(strip_tags($data['last_name']));
        $email = htmlspecialchars(strip_tags($data['email']));
        $phoneNumber = htmlspecialchars(strip_tags($data['phone_number']));

        $stmt->bindParam(":first_name", $firstName);
        $stmt->bindParam(":last_name", $lastName);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":phone_number", $phoneNumber);
        $stmt->bindParam(":password_hash", $data['password_hash']); 
        
        return $stmt->execute();
    }
    
    public function findAdminByEmail($email) {
        $query = "SELECT admin_id, email, password_hash, full_name, role
                      FROM " . $this->admins_table . "
                      WHERE email = :email
                      LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $email = htmlspecialchars(strip_tags($email));
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>