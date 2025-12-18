<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    $redirect_path = $_SESSION['user_type'] === 'admin' ? 'admin_dashboard.php' : 'user_dashboard.php';
    header("Location: " . $redirect_path);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Selection - Bus Ticket Booking System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

    <div class="w-full max-w-lg bg-white rounded-xl shadow-2xl p-10">
        
        <h1 class="text-3xl font-bold text-center text-indigo-700 mb-2">Welcome to Bus Booking</h1>
        <p class="text-center text-gray-500 mb-10">Please select your login type to continue.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div class="border border-gray-200 rounded-lg p-6 text-center hover:shadow-lg transition duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-indigo-500 mx-auto mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                <h2 class="text-xl font-semibold mb-2 text-gray-800">Passenger Login</h2>
                <p class="text-sm text-gray-600 mb-4">Book tickets and view reservations.</p>
                <a href="login.php" class="inline-block w-full py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Login as Passenger
                </a>
            </div>

            <div class="border border-gray-200 rounded-lg p-6 text-center hover:shadow-lg transition duration-300 bg-gray-50">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-red-500 mx-auto mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
                <h2 class="text-xl font-semibold mb-2 text-gray-800">Administrator Login</h2>
                <p class="text-sm text-gray-600 mb-4">Manage the system and user accounts.</p>
                <a href="admin_login.php" class="inline-block w-full py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    Login as Admin
                </a>
            </div>

        </div>
        
        <div class="text-center mt-8">
            <a href="register.php" class="text-sm text-indigo-600 hover:text-indigo-500 font-medium">
                New User? Sign Up for an Account
            </a>
        </div>

    </div>
    
</body>
</html>