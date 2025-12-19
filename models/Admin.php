<?php
// models/Admin.php

require_once __DIR__ . '/../config/Database.php';

class Admin {
    
    // 1. DEFINE CLASS PROPERTIES HERE
    private $conn;
    private $admin_table = "admins"; 
    private $bookings_table = "bookings";
    private $schedules_table = "schedules";
    private $routes_table = "routes";
    private $buses_table = "buses";
    private $booking_seats_table = "booking_seats";

    public function __construct() {
        // 2. INITIALIZE CONNECTION INSIDE THE CONSTRUCTOR
        $database = new Database();
        $this->conn = $database->connect();
    }
    
    // --- AUTHENTICATION METHODS ---

    /**
     * Finds an admin record by email for login.
     * NOTE: This is configured for INSECURE (plain text) login based on your last request.
     * @param string $email
     * @return array|false Admin details or false if not found.
     */
    public function findAdminByEmail($email) {
        $query = "
            SELECT 
                admin_id, 
                email, 
                full_name,          
                password AS plain_password  /* CORRECTION: Changed from password_hash to password */
            FROM 
                " . $this->admin_table . "
            WHERE 
                email = :email
            LIMIT 1"; // Changed LIMIT 0,1 to LIMIT 1 (modern SQL)

        try {
            $stmt = $this->conn->prepare($query); 
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            // Fetch the record. Returns false if no row is found.
            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch(PDOException $e) {
            // Log error: error_log("Admin lookup error: " . $e->getMessage());
            // If the connection is bad or the table/column names are wrong, this returns false.
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
            
            $query_routes = "
                SELECT COUNT(DISTINCT r.route_id) 
                FROM " . $this->routes_table . " r
                JOIN " . $this->schedules_table . " s ON r.route_id = s.route_id 
                WHERE s.departure_time >= NOW()";
            $stats['Active Routes'] = $this->conn->query($query_routes)->fetchColumn();
            
            $query_total_seats = "SELECT SUM(capacity) FROM " . $this->buses_table;
            $total_seats = $this->conn->query($query_total_seats)->fetchColumn();

            $query_booked_seats = "
                SELECT COUNT(bs.id)
                FROM " . $this->booking_seats_table . " bs
                JOIN " . $this->bookings_table . " b ON bs.booking_id = b.booking_id
                WHERE b.status = 'Confirmed'
                AND b.booking_time >= DATE_SUB(NOW(), INTERVAL 7 DAY)";

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