<?php

require_once __DIR__ . '/../config/Database.php';

class Bus {
    private $conn;

    private $bookings_table = "bookings";
    private $schedules_table = "schedules";
    private $routes_table = "routes";
    private $buses_table = "buses";
    private $booking_seats_table = "booking_seats";
    private $users_table = "users";

    public function __construct() {
        // Initialize the database connection
        $database = new Database();
        $this->conn = $database->connect();
    }

    // --- Core Search Function (Used by search_results.php) ---

    /**
     * Searches for available bus schedules based on route and date.
     * Calculates remaining seats for each schedule.
     * * @param string $from Departure location.
     * @param string $to Destination location.
     * @param string $date Date of travel (Y-m-d).
     * @return array|false List of matching schedules or false on error.
     */
    public function searchSchedules($from, $to, $date) {
        $start_datetime = $date . ' 00:00:00';
        $end_datetime = $date . ' 23:59:59';
        
        $query = "
            SELECT 
                s.schedule_id, 
                s.departure_time, 
                s.price, 
                r.departure_location, 
                r.destination_location,
                b.capacity AS bus_capacity,
                b.operator_name,
                b.plate_number,
                -- Subquery to calculate the count of confirmed booked seats for this schedule
                (
                    SELECT COUNT(bs.id)
                    FROM " . $this->booking_seats_table . " bs
                    JOIN " . $this->bookings_table . " bk ON bs.booking_id = bk.booking_id
                    WHERE bk.schedule_id = s.schedule_id 
                    AND bk.status = 'Confirmed'
                ) AS booked_seats
            FROM 
                " . $this->schedules_table . " s
            JOIN 
                " . $this->routes_table . " r ON s.route_id = r.route_id
            JOIN 
                " . $this->buses_table . " b ON s.bus_id = b.bus_id
            WHERE 
                r.departure_location = :from_loc AND 
                r.destination_location = :to_loc AND
                s.departure_time BETWEEN :start_dt AND :end_dt
            ORDER BY 
                s.departure_time ASC";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':from_loc', $from);
            $stmt->bindParam(':to_loc', $to);
            $stmt->bindParam(':start_dt', $start_datetime);
            $stmt->bindParam(':end_dt', $end_datetime);
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch(PDOException $e) {
            return false;
        }
    }
    

     * @param int 
     * @return array 
     */
    public function getBookingHistory($user_id) {
        if (empty($user_id)) {
            return [];
        }

        $query = "
            SELECT 
                b.booking_id,
                b.booking_time,
                b.total_amount,
                b.status,
                s.departure_time,
                r.departure_location,
                r.destination_location,
                bu.operator_name AS bus_operator,
                -- Count the number of seats booked for this booking ID
                (
                    SELECT COUNT(bs.id) 
                    FROM " . $this->booking_seats_table . " bs 
                    WHERE bs.booking_id = b.booking_id
                ) AS seat_count
            FROM 
                " . $this->bookings_table . " b
            JOIN 
                " . $this->schedules_table . " s ON b.schedule_id = s.schedule_id
            JOIN 
                " . $this->routes_table . " r ON s.route_id = r.route_id
            JOIN 
                " . $this->buses_table . " bu ON s.bus_id = bu.bus_id
            WHERE 
                b.user_id = :user_id
            ORDER BY 
                s.departure_time DESC";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch(PDOException $e) {
            return [];
        }
    }
    
   
}