<?php
// controllers/AuthController.php

// Include the User model to interact with the database tables
require_once __DIR__ . '/../models/User.php';
// Include the new Admin model
require_once __DIR__ . '/../models/Admin.php'; // NEW

class AuthController {
    private $userModel;
    private $adminModel; // NEW

    /**
     * Constructor - initialize the User and Admin models
     */
    public function __construct() {
        $this->userModel = new User();
        $this->adminModel = new Admin(); // NEW
    }

    /**
     * Handles the login process for both users (passengers) and admins.
     * @param string $loginType 'user' or 'admin'
     * @param string $email The submitted email
     * @param string $password The submitted plain text password
     * @return array Contains 'success' (bool) and 'message' (string) on failure (redirects on success)
     */
    public function handleLogin($loginType, $email, $password) {
        if (empty($email) || empty($password)) {
            return ['success' => false, 'message' => "Email and password are required."];
        }

        $record = null;
        $id_field = '';
        $redirect_path = '';
        
        // 1. Find the user record based on login type
        if ($loginType === 'admin') {
            // Use the dedicated Admin model for admin lookups
            $record = $this->adminModel->findAdminByEmail($email); // UPDATED CALL
            $id_field = 'admin_id';
            $redirect_path = 'admin_dashboard.php';
        } else { // Default to user (passenger) login
            $record = $this->userModel->findUserByEmail($email);
            $id_field = 'user_id';
            $redirect_path = 'user_dashboard.php';
        }

        // 2. Check if a record was found
        if (!$record) {
            return ['success' => false, 'message' => "Invalid credentials. Account not found."];
        }

        // 3. Verify the password hash
        $password_hash = $record['password_hash'];

        if (password_verify($password, $password_hash)) {
            // Authentication successful!
            
            // 4. Start a session and store user data
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            
            // Store necessary data in the session
            $_SESSION['logged_in'] = true;
            $_SESSION['user_type'] = $loginType;
            $_SESSION['user_id'] = $record[$id_field]; // Use user_id for passenger, admin_id for admin
            
            // Store a friendly name for display
            if ($loginType === 'admin') {
                $_SESSION['name'] = $record['full_name'];
            } else {
                $_SESSION['name'] = $record['first_name'] . ' ' . $record['last_name'];
            }

            // 5. Redirect the user
            header("Location: " . $redirect_path);
            exit();

        } else {
            // Password verification failed
            return ['success' => false, 'message' => "Invalid credentials. Password does not match."];
        }
    }

    /**
     * Handles the user (passenger) registration process.
     * @param array $data Contains form inputs (name, email, password, etc.)
     * @return array Contains 'success' (bool) and 'message' (string) on failure (redirects on success)
     */
    public function register($data) {
        $firstName = trim($data['first_name'] ?? '');
        $lastName = trim($data['last_name'] ?? '');
        $email = trim($data['email'] ?? '');
        $phone = trim($data['phone_number'] ?? '');
        $password = $data['password'] ?? '';
        $confirmPassword = $data['confirm_password'] ?? '';
        
        // --- Input Validation ---
        if (empty($firstName) || empty($lastName) || empty($email) || empty($phone) || empty($password) || empty($confirmPassword)) {
            return ['success' => false, 'message' => "All fields are required."];
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => "Invalid email format."];
        }
        
        // Basic password strength check
        if (strlen($password) < 8) {
            return ['success' => false, 'message' => "Password must be at least 8 characters long."];
        }

        if ($password !== $confirmPassword) {
            return ['success' => false, 'message' => "Passwords do not match."];
        }

        // --- Data Preparation ---
        
        // Hash the password securely
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $registration_data = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone_number' => $phone,
            'password_hash' => $password_hash
        ];

        // --- Database Insertion ---
        
        // The Model handles the insertion and checks for duplicates (e.g., existing email)
        if ($this->userModel->registerUser($registration_data)) {
            // Success: Automatically attempt to log the user in after successful registration
            return $this->handleLogin('user', $email, $password);
            
        } else {
            return ['success' => false, 'message' => "Registration failed. Email or phone number may already be in use."];
        }
    }
}
?>