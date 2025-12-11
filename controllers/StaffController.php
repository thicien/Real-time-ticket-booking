<?php
// controllers/StaffController.php

require_once __DIR__ . '/../models/Staff.php';

class StaffController {
    private $staffModel;

    public function __construct() {
        $this->staffModel = new Staff();
    }

    /**
     * Retrieves all staff members.
     * @return array
     */
    public function index() {
        return $this->staffModel->readAll();
    }
    
    /**
     * Retrieves a staff member by ID.
     * @param int $id
     * @return array|false
     */
    public function getStaffById($id) {
        return $this->staffModel->readOne($id);
    }
    
    /**
     * Handles the creation of a new staff member.
     * @param array $data POST data
     * @return array
     */
    public function createStaff($data) {
        $required_fields = ['name', 'email', 'phone', 'password', 'user_type', 'employee_id'];
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'message' => "Required field '{$field}' is missing."];
            }
        }
        
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => "Invalid email format."];
        }

        // Ensure user_type is valid for staff
        $valid_types = ['driver', 'staff'];
        if (!in_array($data['user_type'], $valid_types)) {
            return ['success' => false, 'message' => "Invalid user type selected."];
        }

        if ($this->staffModel->create($data)) {
            return ['success' => true, 'message' => "Staff member created successfully."];
        } else {
            return ['success' => false, 'message' => "Failed to create staff member. Email or Employee ID might already exist."];
        }
    }
    
    /**
     * Handles the update of an existing staff member.
     * @param array $data POST data
     * @return array
     */
    public function updateStaff($data) {
        $required_fields = ['user_id', 'name', 'email', 'phone', 'user_type', 'employee_id'];
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'message' => "Required field '{$field}' is missing."];
            }
        }
        
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => "Invalid email format."];
        }
        
        // Ensure user_type is valid for staff
        $valid_types = ['driver', 'staff'];
        if (!in_array($data['user_type'], $valid_types)) {
            return ['success' => false, 'message' => "Invalid user type selected."];
        }

        if ($this->staffModel->update($data)) {
            return ['success' => true, 'message' => "Staff member updated successfully."];
        } else {
            return ['success' => false, 'message' => "Failed to update staff member. Check if the user ID exists."];
        }
    }
    
    /**
     * Handles the deletion of a staff member.
     * @param int $id
     * @return array
     */
    public function deleteStaff($id) {
        if (!is_numeric($id) || $id <= 0) {
            return ['success' => false, 'message' => "Invalid Staff ID."];
        }
        
        if ($this->staffModel->delete($id)) {
            return ['success' => true, 'message' => "Staff member deleted successfully."];
        } else {
            return ['success' => false, 'message' => "Failed to delete staff member. Ensure the ID is correct and they are not linked to critical records."];
        }
    }
}
?>