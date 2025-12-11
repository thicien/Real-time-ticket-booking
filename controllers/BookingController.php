<?php
// controllers/BookingController.php (Previously Passenger BusController)

// Requires the passenger-facing model, renamed to Booking.php
require_once 'models/Booking.php';

class BookingController {
    private $bookingModel;

    public function __construct() {
        // Use the renamed model class
        $this->bookingModel = new Booking();
    }

    // Existing method to display search results
    public function getSearchResults($from, $to, $date) {
        if (empty($from) || empty($to) || empty($date)) {
            return false;
        }
        return $this->bookingModel->searchRoutes($from, $to, $date);
    }
    
    /**
     * Fetches detailed information for a specific trip, including seat availability.
     * @param int $scheduleId The ID of the specific bus schedule.
     * @return array|false An array containing bus details and seat data, or false on error.
     */
    public function getScheduleDetailsWithSeats($scheduleId) {
        if (!is_numeric($scheduleId) || $scheduleId <= 0) {
            return false;
        }

        // Fetch the trip details (bus type, total seats, price, etc.)
        $details = $this->bookingModel->getScheduleDetails($scheduleId);
        
        if (!$details) {
            return false;
        }

        // Fetch the list of already booked seats for this schedule
        $bookedSeats = $this->bookingModel->getBookedSeats($scheduleId);

        // Calculate available seats (simple subtraction for now)
        $details['available_seats'] = $details['total_seats'] - count($bookedSeats);
        $details['booked_seats'] = $bookedSeats;

        return $details;
    }
    
    // You would add more passenger-related methods here (e.g., handleBooking, completePayment, etc.)
}