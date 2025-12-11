<?php
// controllers/BusController.php (Admin Management Controller)

// Requires the Admin-facing model for Bus CRUD operations
require_once __DIR__ . '/../models/BusManagement.php';

class BusController {
    private $busModel;

    public function __construct() {
        // Initializes with the dedicated Admin Bus Management Model
        $this->busModel = new BusManagement();
    }

    /**
     * Gets all buses for display in the management table. (Used by bus_management.php)
     * @return array
     */
    public function index() {
        return $this->busModel->readAll();
    }
    
    /**
     * Retrieves a single bus by its ID. (Used by bus_management.php for edit mode)
     * @param int $busId
     * @return array|false
     */
    public function getBusById($busId) {
        if (!is_numeric($busId)) {
            return false;
        }
        return $this->busModel->readOne($busId);
    }

    /**
     * Validates input data for bus creation/update.
     * @param array $data
     * @return string|null Error message or null if validation passes.
     */
    private function validateBusData($data) {
        $reg_num = trim($data['registration_number'] ?? '');
        $capacity = filter_var($data['capacity'] ?? '', FILTER_VALIDATE_INT);
        $model = trim($data['model'] ?? '');
        $operator = trim($data['operator_name'] ?? '');

        if (empty($reg_num) || empty($model) || empty($operator)) {
            return "All fields except Capacity are required.";
        }
        
        if ($capacity === false || $capacity <= 0 || $capacity > 100) {
            return "Capacity must be a number between 1 and 100.";
        }
        
        return null; // Validation successful
    }

    /**
     * Creates a new bus. (Used by bus_management.php POST handler)
     * @param array $data POST data.
     * @return array Response array (success: bool, message: string)
     */
    public function createBus($data) {
        $validation_error = $this->validateBusData($data);
        if ($validation_error) {
            return ['success' => false, 'message' => $validation_error];
        }

        if ($this->busModel->create($data)) {
            return ['success' => true, 'message' => "Bus '{$data['registration_number']}' added successfully."];
        } else {
            return ['success' => false, 'message' => "Failed to add bus. The registration number may already exist or there was a database error."];
        }
    }

    /**
     * Updates an existing bus. (Used by bus_management.php POST handler)
     * @param array $data POST data including bus_id.
     * @return array Response array (success: bool, message: string)
     */
    public function updateBus($data) {
        if (!isset($data['bus_id']) || !is_numeric($data['bus_id'])) {
            return ['success' => false, 'message' => "Invalid bus ID for update."];
        }
        
        $validation_error = $this->validateBusData($data);
        if ($validation_error) {
            return ['success' => false, 'message' => $validation_error];
        }

        if ($this->busModel->update($data)) {
            return ['success' => true, 'message' => "Bus '{$data['registration_number']}' updated successfully."];
        } else {
            return ['success' => false, 'message' => "Failed to update bus. Check if the registration number is duplicated."];
        }
    }
    
    /**
     * Deletes a bus record. (Used by bus_management.php POST handler)
     * @param int $busId
     * @return array Response array (success: bool, message: string)
     */
    public function deleteBus($busId) {
        if (!is_numeric($busId)) {
            return ['success' => false, 'message' => "Invalid bus ID."];
        }
        
        if ($this->busModel->delete($busId)) {
            return ['success' => true, 'message' => "Bus ID {$busId} deleted successfully."];
        } else {
            return ['success' => false, 'message' => "Failed to delete bus. It might be linked to existing schedules."];
        }
    }
}