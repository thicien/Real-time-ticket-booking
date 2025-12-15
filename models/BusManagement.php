<?php
require_once __DIR__ . '/../config/Database.php';

class BusManagement {
    private $conn;
    private $table = "buses";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function readAll() {
        $query = "SELECT bus_id, bus_plate, total_seats, bus_type, bus_operator FROM " . $this->table . " ORDER BY bus_plate ASC";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (bus_plate, total_seats, bus_operator, bus_type, rows, columns, amenities) 
                  VALUES (:bus_plate, :total_seats, :bus_operator, :bus_type, :rows, :columns, :amenities)";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':bus_plate', $data['registration_number']); 
            $stmt->bindParam(':total_seats', $data['capacity'], PDO::PARAM_INT); 
            $stmt->bindParam(':bus_operator', $data['operator_name']); 
            $stmt->bindParam(':bus_type', $data['model']);           
            $stmt->bindParam(':rows', $data['rows'], PDO::PARAM_INT);
            $stmt->bindParam(':columns', $data['columns'], PDO::PARAM_INT);
            $stmt->bindParam(':amenities', $data['amenities']);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Bus Create Error (Code: {$e->getCode()}): " . $e->getMessage()); 
            return false;
        }
    }

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
            $stmt->bindParam(':rows', $data['rows'], PDO::PARAM_INT);
            $stmt->bindParam(':columns', $data['columns'], PDO::PARAM_INT);
            $stmt->bindParam(':amenities', $data['amenities']);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Bus Update Error (Code: {$e->getCode()}): " . $e->getMessage()); 
            return false;
        }
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE bus_id = :bus_id";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':bus_id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>
