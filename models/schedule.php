<?php

require_once __DIR__ . '/../config/Database.php';

class Schedule {
    private $conn;
    private $table = "schedules";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

     @return array
     
    public function getAllBuses() {
        $query = "SELECT bus_id, registration_number, capacity FROM buses ORDER BY registration_number ASC";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

      @return array
    
    public function getAllRoutes() {
        $query = "SELECT route_id, route_name FROM routes ORDER BY route_name ASC";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }


     * @return array Array
     
    public function readAll() {
        $query = "SELECT 
                    s.schedule_id, 
                    s.departure_time, 
                    s.status, 
                    b.registration_number AS bus_reg,
                    b.capacity,
                    r.route_name,
                    r.departure_location,
                    r.destination_location,
                    r.fare_base
                  FROM " . $this->table . " s
                  JOIN buses b ON s.bus_id = b.bus_id
                  JOIN routes r ON s.route_id = r.route_id
                  ORDER BY s.departure_time DESC";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    
      @param array 
      @return bool 
  
    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (bus_id, route_id, departure_time, status) 
                  VALUES (:bus_id, :route_id, :dep_time, :status)";
        
        try {
            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':bus_id', $data['bus_id'], PDO::PARAM_INT);
            $stmt->bindParam(':route_id', $data['route_id'], PDO::PARAM_INT);
            $stmt->bindParam(':dep_time', $data['departure_time']);
            $stmt->bindParam(':status', $data['status']);

            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    
      @param int 
     @return array|false
     
    public function readOne($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE schedule_id = :schedule_id LIMIT 0,1";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':schedule_id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

   
   
     @param array 
     @return bool
    
    public function update($data) {
        $query = "UPDATE " . $this->table . "
                  SET bus_id = :bus_id,
                      route_id = :route_id,
                      departure_time = :dep_time,
                      status = :status
                  WHERE schedule_id = :schedule_id";
        
        try {
            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':schedule_id', $data['schedule_id'], PDO::PARAM_INT);
            $stmt->bindParam(':bus_id', $data['bus_id'], PDO::PARAM_INT);
            $stmt->bindParam(':route_id', $data['route_id'], PDO::PARAM_INT);
            $stmt->bindParam(':dep_time', $data['departure_time']);
            $stmt->bindParam(':status', $data['status']);

            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Deletes a schedule record by ID.
     * @param int $id The schedule_id.
     * @return bool True on success, false on failure.
     */
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE schedule_id = :schedule_id";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':schedule_id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false; 
        }
    }
}