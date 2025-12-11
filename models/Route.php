<?php
// models/Route.php

require_once __DIR__ . '/../config/Database.php';

class Route {
    private $conn;
    private $table = "routes";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    // --- CRUD Methods for Route Management ---

    /**
     * Retrieves all routes.
     * @return array Array of route records.
     */
    public function readAll() {
        $query = "SELECT route_id, route_name, departure_location, destination_location, fare_base FROM " . $this->table . " ORDER BY route_name ASC";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // error_log("Route Read All Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Creates a new route record.
     * @param array $data Contains route_name, departure_location, destination_location, fare_base.
     * @return bool True on success, false on failure.
     */
    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (route_name, departure_location, destination_location, fare_base) 
                  VALUES (:route_name, :dep_loc, :dest_loc, :fare)";
        
        try {
            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':route_name', $data['route_name']);
            $stmt->bindParam(':dep_loc', $data['departure_location']);
            $stmt->bindParam(':dest_loc', $data['destination_location']);
            $stmt->bindParam(':fare', $data['fare_base']);

            return $stmt->execute();
        } catch (PDOException $e) {
            // Check for duplicate entry error (if route_name is unique)
            if ($e->getCode() === '23000') {
                return false; 
            }
            return false;
        }
    }

    /**
     * Reads a single route record by ID.
     * @param int $id The route_id.
     * @return array|false Route record or false if not found.
     */
    public function readOne($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE route_id = :route_id LIMIT 0,1";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':route_id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Updates an existing route record.
     * @param array $data Contains route_id, route_name, departure_location, destination_location, fare_base.
     * @return bool True on success, false on failure.
     */
    public function update($data) {
        $query = "UPDATE " . $this->table . "
                  SET route_name = :route_name,
                      departure_location = :dep_loc,
                      destination_location = :dest_loc,
                      fare_base = :fare
                  WHERE route_id = :route_id";
        
        try {
            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':route_id', $data['route_id'], PDO::PARAM_INT);
            $stmt->bindParam(':route_name', $data['route_name']);
            $stmt->bindParam(':dep_loc', $data['departure_location']);
            $stmt->bindParam(':dest_loc', $data['destination_location']);
            $stmt->bindParam(':fare', $data['fare_base']);

            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Deletes a route record by ID.
     * @param int $id The route_id.
     * @return bool True on success, false on failure.
     */
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE route_id = :route_id";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':route_id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            // Failure usually means the route is referenced by a schedule (Foreign Key constraint)
            return false; 
        }
    }
}