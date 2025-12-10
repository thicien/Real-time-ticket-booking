<?php
require_once 'models/Bus.php';

class BusController {
    private $busModel;

    public function __construct() {
        $this->busModel = new Bus();
    }

    // Existing method to display search results
    public function getSearchResults($from, $to, $date) {
        if (empty($from) || empty($to) || empty($date)) {
            return false;
        }
        return $this->busModel->searchRoutes($from, $to, $date);
    }
    
    // THE MISSING METHOD CAUSING THE ERROR
    /**
     * Fetches detailed information for a specific trip, including seat availability.
     * * @param int $scheduleId The ID of the specific bus schedule.
     * @return array|false An array containing bus details and seat data, or false on error.
     */
    public function getScheduleDetailsWithSeats($scheduleId) {
        if (!is_numeric($scheduleId) || $scheduleId <= 0) {
            return false;
        }

        // Fetch the trip details (bus type, total seats, price, etc.)
        $details = $this->busModel->getScheduleDetails($scheduleId);
        
        if (!$details) {
            return false;
        }

        // Fetch the list of already booked seats for this schedule
        $bookedSeats = $this->busModel->getBookedSeats($scheduleId);

        // Calculate available seats (simple subtraction for now)
        $details['available_seats'] = $details['total_seats'] - count($bookedSeats);
        $details['booked_seats'] = $bookedSeats;

        return $details;
    }
}
?>