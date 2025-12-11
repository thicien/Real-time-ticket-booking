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
     * Updated to use YOUR table's column names: bus_plate, total_seats, bus_operator, bus_type.
     * @return array Array of bus records.
     */
    public function readAll() {
        $query = "SELECT bus_id, bus_plate, total_seats, bus_type, bus_operator FROM " . $this->table . " ORDER BY bus_plate ASC";
        
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
     * Uses YOUR table's column names in the INSERT query.
     * Assumes $data keys match the old generic names (e.g., 'registration_number') for form compatibility.
     * @param array $data Contains registration_number, capacity, model, operator_name, rows, columns, amenities.
     * @return bool True on success, false on failure.
     */
    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (bus_plate, total_seats, bus_operator, bus_type, rows, columns, amenities) 
                  VALUES (:bus_plate, :total_seats, :bus_operator, :bus_type, :rows, :columns, :amenities)";
        
        try {
            $stmt = $this->conn->prepare($query);

            // BINDING: We map the generic input keys (from form) to your specific DB columns:
            
            // Registration number -> bus_plate
            $stmt->bindParam(':bus_plate', $data['registration_number']); 
            
            // Capacity -> total_seats
            $stmt->bindParam(':total_seats', $data['capacity'], PDO::PARAM_INT); 
            
            // Operator Name -> bus_operator
            $stmt->bindParam(':bus_operator', $data['operator_name']); 
            
            // Model -> bus_type
            $stmt->bindParam(':bus_type', $data['model']);           
            
            // New required fields: rows, columns, amenities
            $stmt->bindParam(':rows', $data['rows'], PDO::PARAM_INT);
            $stmt->bindParam(':columns', $data['columns'], PDO::PARAM_INT);
            $stmt->bindParam(':amenities', $data['amenities']);

            return $stmt->execute();
        } catch (PDOException $e) {
            // CRITICAL DEBUGGING LINE: This captures the exact database error.
            error_log("Bus Create Error (Code: {$e->getCode()}): " . $e->getMessage()); 
            
            // Return false for any database error, triggering the generic message
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
     * Updated to use YOUR table's column names.
     * @param array $data Contains bus_id, registration_number, capacity, model, operator_name.
     * @return bool True on success, false on failure.
     */
    public function update($data) {
        $query = "UPDATE " . $this->table . "
                  SET bus_plate = :bus_plate,
                      total_seats = :total_seats,
                      bus_type = :bus_type,
                      bus_operator = :bus_operator,
                      rows = :rows,
                      columns = :columns,
                      amenities = :amenities
                  WHERE bus_id = :bus_id";
        
        try {
            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':bus_id', $data['bus_id'], PDO::PARAM_INT);
            
            $stmt->bindParam(':bus_plate', $data['registration_number']);
            $stmt->bindParam(':total_seats', $data['capacity'], PDO::PARAM_INT);
            $stmt->bindParam(':bus_type', $data['model']);
            $stmt->bindParam(':bus_operator', $data['operator_name']);
            
            // New required fields for update
            $stmt->bindParam(':rows', $data['rows'], PDO::PARAM_INT);
            $stmt->bindParam(':columns', $data['columns'], PDO::PARAM_INT);
            $stmt->bindParam(':amenities', $data['amenities']);

            return $stmt->execute();
        } catch (PDOException $e) {
            // Log if an update fails (could be unique constraint violation)
            error_log("Bus Update Error (Code: {$e->getCode()}): " . $e->getMessage()); 
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
            // error_log("Bus Delete Error (Code: {$e->getCode()}): " . $e->getMessage());
            return false;
        }
    }
}
?>