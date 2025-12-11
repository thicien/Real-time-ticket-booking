<?php
// Include the Database connection class
require_once __DIR__ . '/../config/Database.php';

class Bus {
    private $conn;
    private $routes_table = "routes";
    private $schedules_table = "schedules";
    private $buses_table = "buses"; 
    
    // Define table names for booking logic
    private $bookings_table = "bookings";
    private $booking_seats_table = "booking_seats";
    private $users_table = "users"; // Added users table reference

    /**
     * Constructor - initializes database connection
     */
    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    /**
     * Searches for available bus schedules based on route and date.
     * @param string $departure
     * @param string $destination
     * @param string $date (format: YYYY-MM-DD)
     * @return array Array of matching schedules.
     */
    public function searchRoutes($departure, $destination, $date) {
        $query = "
            SELECT
                s.schedule_id,
                r.route_name,
                r.route_id,
                r.departure_location,
                r.destination_location,
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
            $stmt->bindParam(':departure', $departure);
            $stmt->bindParam(':destination', $destination);
            $stmt->bindParam(':travel_date', $date);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch(PDOException $e) {
            // Log error: echo "Error: " . $e->getMessage();
            return []; 
        }
    }
    
    // --- Methods for Seat Selection (Used by BusController) ---

    /**
     * Fetches the detailed information for a single schedule ID.
     * @param int $scheduleId
     * @return array|false The schedule details or false if not found.
     */
    public function getScheduleDetails($scheduleId) {
        $query = "
            SELECT
                s.schedule_id,
                r.route_name,
                r.departure_location,
                r.destination_location,
                b.bus_operator,
                b.bus_type,
                b.amenities,
                s.departure_time,
                s.arrival_time,
                s.price,
                b.total_seats,
                b.rows, 	 
                b.columns 	 
            FROM
                " . $this->schedules_table . " s
            JOIN
                " . $this->routes_table . " r ON s.route_id = r.route_id
            JOIN
                " . $this->buses_table . " b ON s.bus_id = b.bus_id
            WHERE
                s.schedule_id = :schedule_id
            LIMIT 0,1";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':schedule_id', $scheduleId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            // Log error: echo "Error: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Gets a list of seat numbers that have been booked for a specific schedule.
     * @param int $scheduleId
     * @return array List of booked seat numbers (e.g., ['A1', 'B2', 'C1'])
     */
    public function getBookedSeats($scheduleId) {
        $query = "
            SELECT
                bs.seat_number
            FROM
                " . $this->booking_seats_table . " bs
            JOIN
                " . $this->bookings_table . " b ON bs.booking_id = b.booking_id
            WHERE
                b.schedule_id = :schedule_id
            AND
                b.status != 'Cancelled'"; // Exclude cancelled bookings

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':schedule_id', $scheduleId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

        } catch(PDOException $e) {
            // Log error: echo "Error: " . $e->getMessage();
            return [];
        }
    }
    
    // --- Method for Payment Finalization (Used by payment.php) ---
    
    /**
     * Creates a new booking and records the selected seats in the database.
     * @param int $userId
     * @param int $scheduleId
     * @param float $totalAmount
     * @param array $seats Array of seat numbers (e.g., ['B1', 'B2'])
     * @return int|false The new booking ID or false on failure.
     */
    public function createBooking($userId, $scheduleId, $totalAmount, $seats) {
        if (empty($seats)) {
            return false;
        }

        try {
            // Start Transaction to ensure atomicity
            $this->conn->beginTransaction();

            // 1. Insert into the bookings table
            $booking_query = "
                INSERT INTO " . $this->bookings_table . " (user_id, schedule_id, total_amount, status) 
                VALUES (:user_id, :schedule_id, :total_amount, 'Confirmed')"; 

            $stmt = $this->conn->prepare($booking_query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':schedule_id', $scheduleId, PDO::PARAM_INT);
            $stmt->bindParam(':total_amount', $totalAmount);
            $stmt->execute();

            $booking_id = $this->conn->lastInsertId();

            if (!$booking_id) {
                throw new PDOException("Failed to get booking ID.");
            }

            // 2. Insert into the booking_seats table for each seat
            $seat_query = "
                INSERT INTO " . $this->booking_seats_table . " (booking_id, seat_number) 
                VALUES (:booking_id, :seat_number)";
            $seat_stmt = $this->conn->prepare($seat_query);

            foreach ($seats as $seat) {
                $seat_stmt->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
                $seat_stmt->bindParam(':seat_number', $seat);
                $seat_stmt->execute();
            }

            // Commit the transaction
            $this->conn->commit();

            return $booking_id;

        } catch(PDOException $e) {
            // Rollback on any failure
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            // Log error: echo "Error: " . $e->getMessage();
            return false;
        }
    }

    // --- NEW METHOD for SMS Notification (Used by payment.php) ---

    /**
     * Fetches user details including the phone number.
     * @param int $userId
     * @return array|false User details or false if not found.
     */
    public function getUserDetails($userId) {
        $query = "
            SELECT
                user_id,
                name,
                phone_number
            FROM
                " . $this->users_table . "
            WHERE
                user_id = :user_id
            LIMIT 0,1";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            // Log error: echo "Error: " . $e->getMessage();
            return false;
        }
    }

    // --- METHOD for Confirmation Page (Used by confirmation.php) ---

    /**
     * Fetches all details for a confirmed booking, including associated seats.
     * @param int $bookingId
     * @return array|false Complete booking details or false if not found.
     */
    public function getBookingDetails($bookingId) {
        // Query to fetch the main booking, schedule, route, and bus details
        $main_query = "
            SELECT
                b.booking_id,
                b.user_id,
                b.total_amount,
                b.booking_time,
                s.departure_time,
                r.departure_location,
                r.destination_location,
                bus.bus_operator,
                bus.bus_type,
                bus.bus_plate  -- Assuming you have a bus_plate column
            FROM
                " . $this->bookings_table . " b
            JOIN
                " . $this->schedules_table . " s ON b.schedule_id = s.schedule_id
            JOIN
                " . $this->routes_table . " r ON s.route_id = r.route_id
            JOIN
                " . $this->buses_table . " bus ON s.bus_id = bus.bus_id
            WHERE
                b.booking_id = :booking_id
            LIMIT 0,1";

        try {
            $stmt = $this->conn->prepare($main_query);
            $stmt->bindParam(':booking_id', $bookingId, PDO::PARAM_INT);
            $stmt->execute();
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$booking) {
                return false;
            }

            // Query to fetch the specific seats for this booking
            $seats_query = "
                SELECT
                    seat_number
                FROM
                    " . $this->booking_seats_table . "
                WHERE
                    booking_id = :booking_id";
                    
            $seat_stmt = $this->conn->prepare($seats_query);
            $seat_stmt->bindParam(':booking_id', $bookingId, PDO::PARAM_INT);
            $seat_stmt->execute();
            $seats = $seat_stmt->fetchAll(PDO::FETCH_ASSOC);

            // Add seats to the main booking array
            $booking['seats'] = $seats;

            return $booking;

        } catch(PDOException $e) {
            // Log error: echo "Error: " . $e->getMessage();
            return false;
        }
    }

    // --- METHOD FOR USER DASHBOARD (THE FIX FOR THE FATAL ERROR) ---

    /**
     * Fetches the detailed booking history for a given user.
     * @param int $userId The ID of the currently logged-in user.
     * @return array An array of booking records.
     */
    public function getBookingHistory($userId) {
        $query = "
            SELECT
                b.booking_id,
                b.total_amount,
                b.status,
                s.departure_time,
                r.departure_location,
                r.destination_location,
                bus.bus_operator,
                (SELECT COUNT(bs.id) FROM " . $this->booking_seats_table . " bs WHERE bs.booking_id = b.booking_id) AS seat_count
            FROM
                " . $this->bookings_table . " b
            JOIN
                " . $this->schedules_table . " s ON b.schedule_id = s.schedule_id
            JOIN
                " . $this->routes_table . " r ON s.route_id = r.route_id
            JOIN
                " . $this->buses_table . " bus ON s.bus_id = bus.bus_id
            WHERE
                b.user_id = :user_id
            ORDER BY
                s.departure_time DESC
        ";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch(PDOException $e) {
            // Log error: echo "Error: " . $e->getMessage();
            return [];
        }
    }
}
?>