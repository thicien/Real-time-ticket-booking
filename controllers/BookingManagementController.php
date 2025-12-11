<?php
// controllers/BookingManagementController.php (Admin Controller)

// Requires the Admin-specific Booking Model
require_once __DIR__ . '/../models/BookingManager.php';

class BookingManagementController {
    private $bookingModel;

    public function __construct() {
        // Uses the dedicated Admin Model
        $this->bookingModel = new BookingManager();
    }

    /**
     * Retrieves all detailed bookings for the management dashboard.
     * @return array
     */
    public function index() {
        return $this->bookingModel->readAll();
    }
    
    /**
     * Validates and updates the status of a booking record.
     * @param int $bookingId
     * @param string $status ('Pending', 'Confirmed', 'Cancelled')
     * @return array Response array (success: bool, message: string)
     */
    public function updateBookingStatus($bookingId, $status) {
        if (!is_numeric($bookingId)) {
            return ['success' => false, 'message' => "Invalid Booking ID."];
        }

        $validStatuses = ['Pending', 'Confirmed', 'Cancelled'];
        if (!in_array($status, $validStatuses)) {
            return ['success' => false, 'message' => "Invalid status value: " . htmlspecialchars($status)];
        }
        
        if ($this->bookingModel->updateStatus($bookingId, $status)) {
            return ['success' => true, 'message' => "Booking ID {$bookingId} status updated to '{$status}' successfully."];
        } else {
            return ['success' => false, 'message' => "Failed to update booking status. Database error or ID not found."];
        }
    }
}