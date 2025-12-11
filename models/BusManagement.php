<?php
// models/BusManagement.php (Dedicated to Admin CRUD for Buses)

require_once __DIR__ . '/../config/Database.php';

class BusManagement {
    
    private $conn;
    private $table = "buses"; 

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    // --- CRUD Methods for Bus Management ---

    /**
     * Retrieves all buses for the Admin listing.
     * @return array Array of bus records.
     */
    public function readAll() {
        $query = "SELECT bus_id, registration_number, capacity, model, operator_name FROM " . $this->table . " ORDER BY registration_number ASC";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // error_log("Bus Read All Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Creates a new bus record.
     * @param array $data Contains registration_number, capacity, model, operator_name.
     * @return bool True on success, false on failure.
     */
    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (registration_number, capacity, model, operator_name) 
                  VALUES (:reg_num, :capacity, :model, :operator_name)";
        
        try {
            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':reg_num', $data['registration_number']);
            $stmt->bindParam(':capacity', $data['capacity']);
            $stmt->bindParam(':model', $data['model']);
            $stmt->bindParam(':operator_name', $data['operator_name']);

            return $stmt->execute();
        } catch (PDOException $e) {
            // Check for integrity constraint violation (e.g., duplicate registration)
            if ($e->getCode() === '23000') {
                return false; 
            }
            return false;
        }
    }

    /**
     * Reads a single bus record by ID.
     * @param int $id The bus_id.
     * @return array|false Bus record or false if not found.
     */
    public function readOne($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE bus_id = :bus_id LIMIT 0,1";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':bus_id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Updates an existing bus record.
     * @param array $data Contains bus_id, registration_number, capacity, model, operator_name.
     * @return bool True on success, false on failure.
     */
    public function update($data) {
        $query = "UPDATE " . $this->table . "
                  SET registration_number = :reg_num,
                      capacity = :capacity,
                      model = :model,
                      operator_name = :operator_name
                  WHERE bus_id = :bus_id";
        
        try {
            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':bus_id', $data['bus_id'], PDO::PARAM_INT);
            $stmt->bindParam(':reg_num', $data['registration_number']);
            $stmt->bindParam(':capacity', $data['capacity']);
            $stmt->bindParam(':model', $data['model']);
            $stmt->bindParam(':operator_name', $data['operator_name']);

            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Deletes a bus record by ID.
     * @param int $id The bus_id.
     * @return bool True on success, false on failure.
     */
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE bus_id = :bus_id";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':bus_id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            // Failure usually means the bus is referenced by a schedule (Foreign Key constraint)
            return false;
        }
    }
}