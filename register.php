<?php
// =================================================================
// 1. PHP Initialization and Logic
// =================================================================

// === DEBUGGING CODE: DELETE WHEN LIVE ===
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// =======================================

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is already logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: user_dashboard.php");
    exit;
}

require_once __DIR__ . '/controllers/AuthController.php';

$error_message = "";
$success_message = "";

$fields = [
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'phone_number' => ''
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $raw_first_name = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING);
    $raw_last_name = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING);
    $raw_email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $raw_phone_number = filter_input(INPUT_POST, 'phone_number', FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, 'password');
    $confirm_password = filter_input(INPUT_POST, 'confirm_password');

    $fields['first_name'] = trim($raw_first_name ?? '');
    $fields['last_name'] = trim($raw_last_name ?? '');
    $fields['email'] = trim($raw_email ?? '');
    $fields['phone_number'] = trim($raw_phone_number ?? '');
    
    $safe_password = $password ?? '';
    $safe_confirm_password = $confirm_password ?? '';
    
    $registration_data = [
        'first_name' => $fields['first_name'],
        'last_name' => $fields['last_name'],
        'email' => $fields['email'],
        'phone_number' => $fields['phone_number'],
        'password' => $safe_password,      
        'confirm_password' => $safe_confirm_password 
    ];
    
    $authController = new AuthController();
    $result = $authController->register($registration_data);

    if ($result['success']) {
        header("Location: login.php");
        exit;
    } else {
        $error_message = $result['message'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Bus Ticket Booking System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

    <div class="w-full max-w-lg bg-white rounded-xl shadow-2xl p-10">
        <h1 class="text-3xl font-bold text-center text-indigo-700 mb-8">Passenger Registration</h1>
        
        <?php if ($error_message): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6 text-sm" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($error_message); ?></span>
            </div>
        <?php endif; ?>

        <form id="register-form" method="POST" action="register.php" class="space-y-4">

            <div class="flex space-x-4">
                <div class="flex-1">
                    <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                    <input type="text" id="first_name" name="first_name" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                           value="<?php echo htmlspecialchars($fields['first_name']); ?>">
                </div>
                <div class="flex-1">
                    <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                    <input type="text" id="last_name" name="last_name" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                           value="<?php echo htmlspecialchars($fields['last_name']); ?>">
                </div>
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                <input type="email" id="email" name="email" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                       value="<?php echo htmlspecialchars($fields['email']); ?>">
            </div>

            <div>
                <label for="phone_number" class="block text-sm font-medium text-gray-700">Phone Number</label>
                <input type="tel" id="phone_number" name="phone_number" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                       value="<?php echo htmlspecialchars($fields['phone_number']); ?>">
            </div>

            <div class="flex space-x-4">
                <div class="flex-1">
                    <label for="password" class="block text-sm font-medium text-gray-700">Password (min 8 chars)</label>
                    <input type="password" id="password" name="password" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <div class="flex-1">
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
            </div>

            <div class="pt-2">
                <button type="submit"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Register Account
                </button>
            </div>
            
            <div class="text-center pt-4">
                <a href="login.php" class="text-sm text-indigo-600 hover:text-indigo-500">
                    Already have an account? Log In
                </a>
            </div>
        </form>

    </div>
</body>
</html>