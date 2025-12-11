<?php
// controllers/ScheduleController.php

require_once __DIR__ . '/../models/Schedule.php';

class ScheduleController {
    private $scheduleModel;

    public function __construct() {
        $this->scheduleModel = new Schedule();
    }

    // --- Dependency & Listing Functions ---
    
    public function getRoutes() {
        return $this->scheduleModel->getAllRoutes();
    }

    public function getBuses() {
        return $this->scheduleModel->getAllBuses();
    }
    
    /**
     * Gets all detailed schedules for display in the management table.
     * @return array
     */
    public function index() {
        return $this->scheduleModel->readAll();
    }
    
    /**
     * Retrieves a single schedule by its ID (for edit form).
     * @param int $scheduleId
     * @return array|false
     */
    public function getScheduleById($scheduleId) {
        if (!is_numeric($scheduleId)) {
            return false;
        }
        return $this->scheduleModel->readOne($scheduleId);
    }

    // --- CRUD Logic and Validation ---

    /**
     * Validates input data for schedule creation/update.
     * @param array $data
     * @return string|null Error message or null if validation passes.
     */
    private function validateScheduleData($data) {
        $bus_id = filter_var($data['bus_id'] ?? '', FILTER_VALIDATE_INT);
        $route_id = filter_var($data['route_id'] ?? '', FILTER_VALIDATE_INT);
        $departure_time = trim($data['departure_time'] ?? '');
        $status = trim($data['status'] ?? '');

        if ($bus_id === false || $route_id === false) {
            return "Invalid Bus or Route selected.";
        }
        
        if (empty($departure_time)) {
            return "Departure Date and Time is required.";
        }

        // Basic check for future time only required when creating a NEW schedule
        if (($_POST['action'] ?? '') == 'create' && strtotime($departure_time) <= time()) {
            return "Departure time must be set in the future.";
        }

        if (!in_array($status, ['Scheduled', 'Departed', 'Cancelled'])) {
            return "Invalid status value.";
        }
        
        return null; // Validation successful
    }

    /**
     * Creates a new schedule.
     * @param array $data POST data.
     * @return array Response array (success: bool, message: string)
     */
    public function createSchedule($data) {
        $validation_error = $this->validateScheduleData($data);
        if ($validation_error) {
            return ['success' => false, 'message' => $validation_error];
        }
        
        if ($this->scheduleModel->create($data)) {
            return ['success' => true, 'message' => "New schedule created successfully."];
        } else {
            return ['success' => false, 'message' => "Failed to create schedule. Check Bus/Route validity."];
        }
    }

    /**
     * Updates an existing schedule.
     * @param array $data POST data including schedule_id.
     * @return array Response array (success: bool, message: string)
     */
    public function updateSchedule($data) {
        if (!isset($data['schedule_id']) || !is_numeric($data['schedule_id'])) {
            return ['success' => false, 'message' => "Invalid Schedule ID for update."];
        }
        
        $validation_error = $this->validateScheduleData($data);
        if ($validation_error) {
            return ['success' => false, 'message' => $validation_error];
        }

        if ($this->scheduleModel->update($data)) {
            return ['success' => true, 'message' => "Schedule ID {$data['schedule_id']} updated successfully."];
        } else {
            return ['success' => false, 'message' => "Failed to update schedule. Please verify input data."];
        }
    }
    
    /**
     * Deletes a schedule record.
     * @param int $scheduleId
     * @return array Response array (success: bool, message: string)
     */
    public function deleteSchedule($scheduleId) {
        if (!is_numeric($scheduleId)) {
            return ['success' => false, 'message' => "Invalid Schedule ID."];
        }
        
        if ($this->scheduleModel->delete($scheduleId)) {
            return ['success' => true, 'message' => "Schedule ID {$scheduleId} deleted successfully."];
        } else {
            return ['success' => false, 'message' => "Failed to delete schedule. It may be linked to existing passenger bookings."];
        }
    }
}