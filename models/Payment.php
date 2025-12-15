<?php

require_once __DIR__ . '/../config/Database.php';

class Payment {
    private $conn;
    private $table = "payments";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    /**
     * Retrieves all payments with detailed information about the linked booking, schedule, and passenger.
     * @return array
     */
    public function readAll() {
        $query = "SELECT 
                    p.payment_id, 
                    p.booking_id,
                    p.payment_date, 
                    p.payment_amount, 
                    p.payment_status,
                    p.payment_method,
                    p.transaction_reference,
                    u.name AS passenger_name,
                    u.email AS passenger_email,
                    u.phone AS passenger_phone,
                    b.seat_number,
                    b.status AS booking_status,
                    s.departure_time,
                    r.route_name,
                    r.fare_base
                  FROM " . $this->table . " p
                  JOIN bookings b ON p.booking_id = b.booking_id
                  JOIN users u ON b.user_id = u.user_id
                  JOIN schedules s ON b.schedule_id = s.schedule_id
                  JOIN routes r ON s.route_id = r.route_id
                  ORDER BY p.payment_date DESC";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Retrieves a single payment record by ID.
     * @param int $paymentId
     * @return array|false Payment record or false if not found.
     */
    public function readOne($paymentId) {
        $query = "SELECT * FROM " . $this->table . " WHERE payment_id = :id LIMIT 1";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $paymentId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Manually updates the payment status. (Use with caution for reconciliation only)
     * @param int $paymentId
     * @param string $status ('Pending', 'Paid', 'Failed', 'Refunded')
     * @return bool True on success, false on failure.
     */
    public function updateStatus($paymentId, $status) {
        $query = "UPDATE " . $this->table . " SET payment_status = :status WHERE payment_id = :payment_id";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':payment_id', $paymentId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>