<?php
// controllers/AdminController.php

require_once __DIR__ . '/../models/Admin.php';

class AdminController {
    private $adminModel;

    public function __construct() {
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
            $_SESSION['admin_error'] = "Invalid credentials. Admin account not found.";
            header("Location: admin_login.php");
            exit();
        }

        $password_hash = $record['password_hash'];

        if (password_verify($password, $password_hash)) {
            // Authentication successful!
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            
            // Set Admin Session Variables
            $_SESSION['logged_in'] = true;
            $_SESSION['user_type'] = 'admin'; // Crucial for security checks
            $_SESSION['user_id'] = $record['admin_id'];
            $_SESSION['name'] = $record['full_name']; // Matches your DB column

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