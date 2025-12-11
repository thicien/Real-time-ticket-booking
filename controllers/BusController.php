<?php
// controllers/BusController.php (Admin Management Controller)
require_once __DIR__ . '/../models/BusManagement.php';

class BusController {
    private $busModel;

    public function __construct() {
        $this->busModel = new BusManagement();
    }

    /** Fetch all buses */
    public function index() {
        return $this->busModel->readAll();
    }

    /** Fetch single bus */
    public function getBusById($busId) {
        if (!is_numeric($busId)) return false;
        return $this->busModel->readOne($busId);
    }

    /** VALIDATION */
    private function validateBusData($data) {
        $registration = trim($data['registration_number'] ?? '');
        $capacity     = filter_var($data['capacity'] ?? '', FILTER_VALIDATE_INT);
        $model        = trim($data['model'] ?? '');
        $operator     = trim($data['operator_name'] ?? '');
        $rows         = filter_var($data['rows'] ?? '', FILTER_VALIDATE_INT);
        $columns      = filter_var($data['columns'] ?? '', FILTER_VALIDATE_INT);
        $amenities    = trim($data['amenities'] ?? '');

        if ($registration === '' || $model === '' || $operator === '') {
            return "Registration No., Bus Type, and Operator Name are required.";
        }

        if ($capacity === false || $capacity < 1 || $capacity > 100) {
            return "Total Seats must be a number between 1 and 100.";
        }

        if ($rows === false || $rows < 1 || $columns === false || $columns < 1) {
            return "Rows and Columns must be valid positive integers.";
        }

        return null;
    }

    /** CREATE BUS */
    public function createBus($data) {
        $error = $this->validateBusData($data);
        if ($error) return ['success' => false, 'message' => $error];

        // Send data using the keys expected by BusManagement::create()
        $bus_data = [
            'registration_number' => trim($data['registration_number']),
            'capacity'            => (int)$data['capacity'],
            'model'               => trim($data['model']),
            'operator_name'       => trim($data['operator_name']),
            'rows'                => (int)$data['rows'],
            'columns'             => (int)$data['columns'],
            'amenities'           => trim($data['amenities']),
        ];

        if ($this->busModel->create($bus_data)) {
            return [
                'success' => true,
                'message' => "Bus '{$bus_data['registration_number']}' added successfully."
            ];
        }

        return [
            'success' => false,
            'message' => "Failed to add bus. Plate number might already exist."
        ];
    }

    /** UPDATE BUS */
    public function updateBus($data) {
        if (!isset($data['bus_id']) || !is_numeric($data['bus_id']))
            return ['success' => false, 'message' => "Invalid Bus ID."];

        $error = $this->validateBusData($data);
        if ($error) return ['success' => false, 'message' => $error];

        $bus_data = [
            'bus_id'             => (int)$data['bus_id'],
            'registration_number' => trim($data['registration_number']),
            'capacity'           => (int)$data['capacity'],
            'model'              => trim($data['model']),
            'operator_name'      => trim($data['operator_name']),
            'rows'               => (int)$data['rows'],
            'columns'            => (int)$data['columns'],
            'amenities'          => trim($data['amenities']),
        ];

        if ($this->busModel->update($bus_data)) {
            return [
                'success' => true,
                'message' => "Bus '{$bus_data['registration_number']}' updated successfully."
            ];
        }

        return [
            'success' => false,
            'message' => "Failed to update bus. Plate number might be duplicated."
        ];
    }

    /** DELETE BUS */
    public function deleteBus($busId) {
        if (!is_numeric($busId)) {
            return ['success' => false, 'message' => "Invalid Bus ID."];
        }

        if ($this->busModel->delete($busId)) {
            return ['success' => true, 'message' => "Bus ID $busId deleted successfully."];
        }

        return [
            'success' => false,
            'message' => "Failed to delete bus. It may be linked to schedules."
        ];
    }
}
?>
