<?php
// controllers/RouteController.php

require_once __DIR__ . '/../models/Route.php';

class RouteController {
    private $routeModel;

    public function __construct() {
        $this->routeModel = new Route();
    }

    /**
     * Gets all routes for display in the management table.
     * @return array
     */
    public function index() {
        return $this->routeModel->readAll();
    }
    
    /**
     * Retrieves a single route by its ID.
     * @param int $routeId
     * @return array|false
     */
    public function getRouteById($routeId) {
        if (!is_numeric($routeId)) {
            return false;
        }
        return $this->routeModel->readOne($routeId);
    }

    /**
     * Validates input data for route creation/update.
     * @param array $data
     * @return string|null Error message or null if validation passes.
     */
    private function validateRouteData($data) {
        $route_name = trim($data['route_name'] ?? '');
        $dep_loc = trim($data['departure_location'] ?? '');
        $dest_loc = trim($data['destination_location'] ?? '');
        // Use FILTER_VALIDATE_FLOAT for currency/fare
        $fare = filter_var($data['fare_base'] ?? '', FILTER_VALIDATE_FLOAT); 

        if (empty($route_name) || empty($dep_loc) || empty($dest_loc)) {
            return "Route Name, Departure, and Destination are required.";
        }
        
        if ($dep_loc === $dest_loc) {
            return "Departure and Destination locations cannot be the same.";
        }
        
        if ($fare === false || $fare <= 0) {
            return "Base fare must be a positive number.";
        }
        
        return null; // Validation successful
    }

    /**
     * Creates a new route.
     * @param array $data POST data.
     * @return array Response array (success: bool, message: string)
     */
    public function createRoute($data) {
        $validation_error = $this->validateRouteData($data);
        if ($validation_error) {
            return ['success' => false, 'message' => $validation_error];
        }

        if ($this->routeModel->create($data)) {
            return ['success' => true, 'message' => "Route '{$data['route_name']}' added successfully."];
        } else {
            return ['success' => false, 'message' => "Failed to add route. The name may already exist or there was a database error."];
        }
    }

    /**
     * Updates an existing route.
     * @param array $data POST data including route_id.
     * @return array Response array (success: bool, message: string)
     */
    public function updateRoute($data) {
        if (!isset($data['route_id']) || !is_numeric($data['route_id'])) {
            return ['success' => false, 'message' => "Invalid route ID for update."];
        }
        
        $validation_error = $this->validateRouteData($data);
        if ($validation_error) {
            return ['success' => false, 'message' => $validation_error];
        }

        if ($this->routeModel->update($data)) {
            return ['success' => true, 'message' => "Route '{$data['route_name']}' updated successfully."];
        } else {
            return ['success' => false, 'message' => "Failed to update route. Check input and ensure unique name."];
        }
    }
    
    /**
     * Deletes a route record.
     * @param int $routeId
     * @return array Response array (success: bool, message: string)
     */
    public function deleteRoute($routeId) {
        if (!is_numeric($routeId)) {
            return ['success' => false, 'message' => "Invalid route ID."];
        }
        
        if ($this->routeModel->delete($routeId)) {
            return ['success' => true, 'message' => "Route ID {$routeId} deleted successfully."];
        } else {
            return ['success' => false, 'message' => "Failed to delete route. It is likely associated with existing schedules or bookings."];
        }
    }
}