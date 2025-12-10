<?php
// Include the Database connection class
require_once __DIR__ . '/../config/Database.php';

class Bus {
    private $conn;
    private $routes_table = "routes";
    private $schedules_table = "schedules";
    private $buses_table = "buses"; // For bus type, seats, amenities
    // Note: We need a temporary way to track available seats before adding the booking logic

    /**
     * Constructor - initializes database connection
     */
    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    /**
     * Searches for available bus schedules based on route and date.
     * * @param string $departure
     * @param string $destination
     * @param string $date (format: YYYY-MM-DD)
     * @return array Array of matching schedules.
     */
    public function searchRoutes($departure, $destination, $date) {
        // SQL query to join routes, schedules, and buses to get necessary trip details.
        $query = "
            SELECT
                s.schedule_id,
                r.route_name,
                r.route_id,
                b.bus_operator,
                b.bus_type,
                b.amenities,
                s.departure_time,
                s.arrival_time,
                s.price,
                b.total_seats
            FROM
                " . $this->schedules_table . " s
            JOIN
                " . $this->routes_table . " r ON s.route_id = r.route_id
            JOIN
                " . $this->buses_table . " b ON s.bus_id = b.bus_id
            WHERE
                r.departure_location = :departure
            AND
                r.destination_location = :destination
            AND
                DATE(s.departure_time) = :travel_date
            ORDER BY
                s.departure_time ASC";

        try {
            $stmt = $this->conn->prepare($query);

            // Bind sanitized parameters
            $stmt->bindParam(':departure', $departure);
            $stmt->bindParam(':destination', $destination);
            $stmt->bindParam(':travel_date', $date);

            $stmt->execute();
            
            // Return all matching records
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch(PDOException $e) {
            // Log error and return an empty array for safety
            return []; 
        }
    }
    
    // Future methods will include:
    // public function getBookedSeatsCount($scheduleId) { ... }
}
?>