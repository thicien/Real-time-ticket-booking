<?php
// controllers/AdminController.php

// === DEBUGGING CODE: DELETE WHEN LIVE ===
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// =======================================

// Path: relative to the controllers directory, looking for models/Admin.php
require_once __DIR__ . '/../models/Admin.php'; 

class AdminController {
    private $adminModel;

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        // This line fails if models/Admin.php file is not found or Admin class doesn't exist.
        $this->adminModel = new Admin(); 
    }

    /**
     * Handles the administrator login process.
     * @param string $email
     * @param string $password
     */
    public function handleLogin($email, $password) {
        if (empty($email) || empty($password)) {
            $_SESSION['admin_error'] = "Email and password are required.";
            header("Location: admin_login.php");
            exit();
        }

        $record = $this->adminModel->findAdminByEmail($email);

        if (!$record) {
            // Error is generated here because findAdminByEmail() returned nothing.
            $_SESSION['admin_error'] = "Invalid credentials. Admin account not found.";
            header("Location: admin_login.php");
            exit();
        }

        // --- TEMPORARY INSECURE LOGIN CHECK ---
        // $stored_password must contain the password value from the DB.
        $stored_password = $record['plain_password']; 

        // Direct comparison (==) for temporary plain-text mode
        if ($password == $stored_password) {
            // Authentication successful!
            
            // Set Admin Session Variables
            $_SESSION['logged_in'] = true;
            $_SESSION['user_type'] = 'admin'; // Crucial for security checks
            $_SESSION['user_id'] = $record['admin_id'];
            $_SESSION['name'] = $record['full_name'];

            header("Location: admin_dashboard.php");
            exit();

        } else {
            // Password verification failed
            $_SESSION['admin_error'] = "Invalid password.";
            header("Location: admin_login.php");
            exit();
        }
    }
}