<?php
// Include the Bus Model (which we will create right after this)
require_once __DIR__ . '/../models/Bus.php';

class BusController {
    private $busModel;

    /**
     * Constructor - initializes the Bus Model
     */
    public function __construct() {
        $this->busModel = new Bus();
    }

    /**
     * Handles the search request from the user dashboard.
     * * @param string $departure Departure city
     * @param string $destination Destination city
     * @param string $date Date of travel
     * @return array|false Array of matching bus schedules or false if error/no results.
     */
    public function getSearchResults($departure, $destination, $date) {
        // --- Basic Validation ---
        if (empty($departure) || empty($destination) || empty($date)) {
            // In a real app, this should throw an exception or return an error flag
            return false; 
        }
        
        // --- Sanitization and Trimming ---
        $departure = trim(htmlspecialchars(strip_tags($departure)));
        $destination = trim(htmlspecialchars(strip_tags($destination)));
        $date = trim(htmlspecialchars(strip_tags($date)));
        
        // --- Call Model to Fetch Data ---
        $results = $this->busModel->searchRoutes($departure, $destination, $date);
        
        // If results are found, you could add sorting/filtering logic here later.
        return $results;
    }
    
    // Future methods will include:
    // public function getSeatAvailability($scheduleId) { ... }
    // public function processBooking($data) { ... }
}
?>