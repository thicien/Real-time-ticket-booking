<?php
// login.php (Dedicated to Passenger Login and Routing)
// This file assumes a separate 'admin_login.php' handles admin authentication.

// =================================================================
// 1. PHP Initialization and Logic
// =================================================================

// Start the session at the very beginning
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is already logged in and redirect (prevent accessing login page when logged in)
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    // Determine the correct dashboard path based on the session variable
    $redirect_path = $_SESSION['user_type'] === 'admin' ? 'admin_dashboard.php' : 'user_dashboard.php';
    header("Location: " . $redirect_path);
    exit;
}

// Include the necessary controller file
// We only need the AuthController which handles passenger logic
require_once 'controllers/AuthController.php';

$error_message = "";
$email = ''; // To preserve the email input on error

// Handle POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Note: Since we only have the passenger form here, we don't need the 'login_type' input in the form.
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Instantiate the Controller and handle login
    $authController = new AuthController();
    
    // The handleLogin method will redirect on success, or return an array on failure.
    // We explicitly call it for 'user' type here.
    $result = $authController->handleLogin('user', $email, $password);

    if (!$result['success']) {
        // Login failed: set the error message
        $error_message = $result['message'];
    }
    
    // Note: If the login succeeds, the controller handles the redirect and exits PHP execution.
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passenger Login - Bus Ticket Booking System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

    <div class="w-full max-w-md bg-white rounded-xl shadow-2xl p-8">
        
        <h1 class="text-3xl font-bold text-center text-indigo-700 mb-2">Passenger Login</h1>
        
        <p class="text-center text-sm text-gray-500 mb-6">
            <a href="admin_login.php" class="text-indigo-600 hover:text-indigo-500 font-medium">
                Admin Login &rarr;
            </a>
        </p>

        <?php if ($error_message): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 text-sm" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($error_message); ?></span>
            </div>
        <?php endif; ?>

        <form id="login-form" method="POST" action="login.php">

            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                <input type="email" id="email" name="email" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        value="<?php echo htmlspecialchars($email); ?>">
            </div>

            <div class="mb-6">
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" id="password" name="password" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
            
            <div class="flex items-center justify-between mb-6">
                <a href="#" class="text-sm text-indigo-600 hover:text-indigo-500">
                    Forgot Password?
                </a>
                <a href="register.php" id="register-link" class="text-sm text-gray-600 hover:text-gray-500 font-medium">
                    Don't have an account? Sign Up
                </a>
            </div>

            <div>
                <button type="submit"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Login as Passenger
                </button>
            </div>
        </form>

    </div>
    
    </body>
</html>