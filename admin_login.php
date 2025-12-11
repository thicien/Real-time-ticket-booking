<?php
// admin_login.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if admin is already logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && $_SESSION['user_type'] === 'admin') {
    header("Location: admin_dashboard.php");
    exit;
}

require_once 'controllers/AdminController.php';

$error_message = $_SESSION['admin_error'] ?? "";
unset($_SESSION['admin_error']); // Clear the error after displaying

// Handle POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $adminController = new AdminController();
    $adminController->handleLogin($email, $password); 
    // This function redirects on success or sets the error and redirects back to admin_login.php on failure.
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-200 min-h-screen flex items-center justify-center">

    <div class="w-full max-w-md bg-white rounded-xl shadow-2xl p-8 border-t-4 border-indigo-700">
        <h1 class="text-3xl font-bold text-center text-indigo-700 mb-6">Administrator Login</h1>
        <p class="text-center text-sm text-gray-500 mb-6">Access the Bus Booking System Management Panel</p>
        
        <?php if ($error_message): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 text-sm" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($error_message); ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="admin_login.php">
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                <input type="email" id="email" name="email" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>

            <div class="mb-6">
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" id="password" name="password" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
            
            <div>
                <button type="submit"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Login as Admin
                </button>
            </div>
        </form>

        <div class="text-center mt-6">
            <a href="login.php" class="text-sm text-indigo-600 hover:text-indigo-500">
                &larr; Back to Passenger Login
            </a>
        </div>
    </div>
</body>
</html>