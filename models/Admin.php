<?php
// models/Admin.php

require_once __DIR__ . '/../config/Database.php';

class Admin {
    private $conn;
    // Define all necessary tables for Admin functions
    private $admin_table = "admins"; 
    private $bookings_table = "bookings";
    private $schedules_table = "schedules";
    private $routes_table = "routes";
    private $buses_table = "buses";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }
    
    // --- AUTHENTICATION METHODS ---

    /**
     * Finds an admin record by email for login.
     * @param string $email
     * @return array|false Admin details or false if not found.
     */
    public function findAdminByEmail($email) {
        $query = "
            SELECT 
                admin_id, email, name AS full_name, password AS password_hash 
            FROM 
                " . $this->admin_table . "
            WHERE 
                email = :email
            LIMIT 0,1";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            // Log error: error_log("Admin lookup error: " . $e->getMessage());
            return false;
        }
    }


    // --- DASHBOARD STATS METHODS ---

    /**
     * Fetches the core statistics for the Admin Dashboard.
     * @return array Associative array of statistics.
     */
    public function getDashboardStats() {
        $stats = [];
        
        try {
            // 1. Total Bookings
            $query_bookings = "SELECT COUNT(booking_id) FROM " . $this->bookings_table . " WHERE status != 'Cancelled'";
            $stats['Total Bookings'] = $this->conn->query($query_bookings)->fetchColumn();

            // 2. Total Revenue (Assuming total_amount is stored in bookings)
            $query_revenue = "SELECT SUM(total_amount) FROM " . $this->bookings_table . " WHERE status = 'Confirmed'";
            $stats['Total Revenue'] = $this->conn->query($query_revenue)->fetchColumn() ?? 0;
            
            // 3. Active Routes (Routes that have schedules)
            $query_routes = "
                SELECT COUNT(DISTINCT r.route_id) 
                FROM " . $this->routes_table . " r
                JOIN " . $this->schedules_table . " s ON r.route_id = s.route_id 
                WHERE s.departure_time >= NOW()"; // Only count routes with future schedules
            $stats['Active Routes'] = $this->conn->query($query_routes)->fetchColumn();
            
            // 4. Seat Occupancy (Requires a more complex calculation: Booked Seats / Total Available Seats in upcoming schedules)
            // This is a simplified calculation, a real one would be complex and slow.
            // Simplified: Number of Confirmed Booking Seats / Total Seats of all Buses
            
            // a) Total Seats in System
            $query_total_seats = "SELECT SUM(total_seats) FROM " . $this->buses_table;
            $total_seats = $this->conn->query($query_total_seats)->fetchColumn();

            // b) Total Booked Seats (Confirmed, future or recent)
            $query_booked_seats = "
                SELECT COUNT(bs.id)
                FROM booking_seats bs
                JOIN " . $this->bookings_table . " b ON bs.booking_id = b.booking_id
                WHERE b.status = 'Confirmed'
                AND b.booking_time >= DATE_SUB(NOW(), INTERVAL 7 DAY)"; // Count bookings in the last 7 days for a reasonable avg

            $booked_seats = $this->conn->query($query_booked_seats)->fetchColumn();
            
            if ($total_seats > 0) {
                $occupancy_rate = ($booked_seats / $total_seats) * 100;
                $stats['Seat Occupancy'] = round($occupancy_rate, 1) . '%';
            } else {
                $stats['Seat Occupancy'] = '0%';
            }

            return $stats;

        } catch(PDOException $e) {
            // Log the error and return zeros
            // error_log("Admin stats error: " . $e->getMessage());
            return [
                'Total Bookings' => 0, 
                'Total Revenue' => 0, 
                'Active Routes' => 0, 
                'Seat Occupancy' => '0%'
            ];
        }
    }
}
?>