<?php
// models/BookingManager.php (Admin Backend Model)

require_once __DIR__ . '/../config/Database.php';

class BookingManager { // Class name updated to BookingManager
    private $conn;
    private $table = "bookings";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    /**
     * Calculates the number of booked seats for a specific schedule.
     * Only counts 'Confirmed' bookings.
     * @param int $scheduleId
     * @return int Number of occupied seats.
     */
    public function getOccupiedSeats($scheduleId) {
        $query = "SELECT COUNT(booking_id) AS occupied_seats 
                  FROM " . $this->table . " 
                  WHERE schedule_id = :schedule_id AND status = 'Confirmed'";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':schedule_id', $scheduleId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($result['occupied_seats'] ?? 0);
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * Retrieves all bookings with detailed information about the schedule, user, and payment.
     * NOTE: s.schedule_id is included to correctly link data for seat calculation later.
     * @return array
     */
    public function readAll() {
        $query = "SELECT 
                    b.booking_id, 
                    b.booking_date, 
                    b.status AS booking_status,
                    b.seat_number,
                    s.schedule_id, 
                    s.departure_time,
                    r.route_name,
                    r.departure_location,
                    r.destination_location,
                    r.fare_base,
                    bu.capacity AS bus_capacity,
                    bu.registration_number AS bus_reg,
                    u.name AS passenger_name,
                    u.email AS passenger_email,
                    u.phone AS passenger_phone,
                    p.payment_amount,
                    p.payment_status,
                    p.payment_method
                  FROM " . $this->table . " b
                  JOIN schedules s ON b.schedule_id = s.schedule_id
                  JOIN routes r ON s.route_id = r.route_id
                  JOIN buses bu ON s.bus_id = bu.bus_id
                  JOIN users u ON b.user_id = u.user_id
                  LEFT JOIN payments p ON b.booking_id = p.booking_id
                  ORDER BY b.booking_id DESC";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // error_log("Booking Read All Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Updates the status of a specific booking.
     * @param int $bookingId
     * @param string $status ('Pending', 'Confirmed', 'Cancelled')
     * @return bool True on success, false on failure.
     */
    public function updateStatus($bookingId, $status) {
        $query = "UPDATE " . $this->table . " SET status = :status WHERE booking_id = :booking_id";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':booking_id', $bookingId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
}