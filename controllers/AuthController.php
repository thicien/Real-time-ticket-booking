<?php
// controllers/AuthController.php (For Passenger Authentication)

// === DEBUGGING CODE: DELETE WHEN LIVE ===
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// =======================================

// Adjust the path to your User model using the magic constant __DIR__
// Path: current_directory/../models/User.php
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $userModel;

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        // NOTE: This will throw a Fatal Error if models/User.php is not found!
        $this->userModel = new User();
    }

    /**
     * Handles passenger registration logic.
     * ... (registration code omitted for brevity but should remain)
     */
    public function register(array $data) {
        // ...
        // (Your existing registration logic here)
        // ...
    }

    /**
     * Handles passenger login logic.
     * @return array {'success': bool, 'message': string, 'redirect': string (on success)}
     */
    public function handleLogin(string $email, string $password) {
        
        $record = $this->userModel->findUserByEmail($email);

        // Check if a record was found AND securely verify the password
        if ($record && password_verify($password, $record['password_hash'])) {
            
            // Authentication successful! Set Session Variables
            $_SESSION['logged_in'] = true;
            $_SESSION['user_type'] = 'passenger';
            $_SESSION['user_id'] = $record['user_id'];
            $_SESSION['name'] = $record['first_name'] . ' ' . $record['last_name'];
            
            // Return success with redirect URL
            return ['success' => true, 'message' => 'Login successful!', 'redirect' => 'user_dashboard.php'];

        } else {
            // Generic message for security
            return ['success' => false, 'message' => 'Invalid email or password.'];
        }
    }

    public function logout() {
        session_unset();
        session_destroy();
        // Redirect to home page or login page
        header("Location: index.php"); // Updated to redirect to the selector page
        exit();
    }
}