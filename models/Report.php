<?php

require_once __DIR__ . '/../config/Database.php';

class Report {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    /**
     * Gets a summary count of all booking statuses.
     * @return array
     */
    public function getBookingStatusSummary() {
        $query = "SELECT 
                    status, 
                    COUNT(booking_id) AS total_count
                  FROM bookings
                  GROUP BY status";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $summary = [
                'Pending' => 0,
                'Confirmed' => 0,
                'Cancelled' => 0,
                'Total' => 0
            ];
            foreach ($results as $row) {
                $status = $row['status'];
                $count = (int)$row['total_count'];
                if (isset($summary[$status])) {
                    $summary[$status] = $count;
                }
                $summary['Total'] += $count;
            }
            return $summary;

        } catch (PDOException $e) {
            return ['Pending' => 0, 'Confirmed' => 0, 'Cancelled' => 0, 'Total' => 0];
        }
    }

    /**
     * Calculates total confirmed revenue within a given date range.
     * @param string $startDate (Y-m-d)
     * @param string $endDate (Y-m-d)
     * @return array Total revenue and number of transactions.
     */
    public function getRevenueReport($startDate, $endDate) {
        $query = "SELECT 
                    SUM(p.payment_amount) AS total_revenue,
                    COUNT(p.payment_id) AS total_transactions
                  FROM payments p
                  JOIN bookings b ON p.booking_id = b.booking_id
                  WHERE p.payment_status = 'Paid' 
                    AND b.status = 'Confirmed'
                    AND DATE(p.payment_date) >= :start_date 
                    AND DATE(p.payment_date) <= :end_date";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            return ['total_revenue' => 0, 'total_transactions' => 0];
        }
    }

    /**
     * Retrieves detailed occupancy data for all future schedules.
     * Calculated as (Confirmed Bookings / Bus Capacity).
     * @return array
     */
    public function getOccupancyReport() {
        $query = "SELECT 
                    s.schedule_id, 
                    s.departure_time,
                    r.route_name,
                    bu.capacity AS bus_capacity,
                    COUNT(CASE WHEN b.status = 'Confirmed' THEN b.booking_id END) AS occupied_seats
                  FROM schedules s
                  JOIN routes r ON s.route_id = r.route_id
                  JOIN buses bu ON s.bus_id = bu.bus_id
                  LEFT JOIN bookings b ON s.schedule_id = b.schedule_id
                  WHERE s.departure_time >= NOW()
                  GROUP BY s.schedule_id, s.departure_time, r.route_name, bu.capacity
                  ORDER BY s.departure_time ASC";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            return [];
        }
    }
}
?>