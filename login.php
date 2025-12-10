<?php
// =================================================================
// 1. PHP Initialization and Logic
// =================================================================

// Start the session at the very beginning
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is already logged in and redirect (prevent accessing login page when logged in)
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    // Determine the correct dashboard path
    $redirect_path = $_SESSION['user_type'] === 'admin' ? 'admin_dashboard.php' : 'user_dashboard.php';
    header("Location: " . $redirect_path);
    exit;
}

// Include the necessary controller file (Path is relative to the current file)
require_once 'controllers/AuthController.php';

$error_message = "";
$login_type = "user"; // Default to passenger login
$email = ''; // To preserve the email input on error

// Handle POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login_type = $_POST['login_type'] ?? 'user';
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Instantiate the Controller and handle login
    $authController = new AuthController();
    
    // The handleLogin method will redirect on success, or return an array on failure.
    $result = $authController->handleLogin($login_type, $email, $password);

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
    <title>Login - Bus Ticket Booking System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Optional: Custom style for the transition effect */
        .tab-transition {
            transition: all 0.3s ease;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

    <div class="w-full max-w-md bg-white rounded-xl shadow-2xl p-8">
        <h1 class="text-3xl font-bold text-center text-indigo-700 mb-6">System Login</h1>
        
        <div class="flex border-b mb-6">
            <button id="user-tab" class="tab-transition flex-1 py-3 text-center text-sm font-medium border-b-2 border-indigo-600 text-indigo-700 hover:text-indigo-800">
                Passenger Login
            </button>
            <button id="admin-tab" class="tab-transition flex-1 py-3 text-center text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700">
                Admin Login
            </button>
        </div>

        <?php if ($error_message): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 text-sm" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($error_message); ?></span>
            </div>
        <?php endif; ?>

        <form id="login-form" method="POST" action="login.php">
            <input type="hidden" name="login_type" id="login_type" value="<?php echo htmlspecialchars($login_type); ?>">

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
            
            <div id="dynamic-fields" class="mb-6">
                </div>

            <div class="flex items-center justify-between mb-6">
                <a href="#" class="text-sm text-indigo-600 hover:text-indigo-500">
                    Forgot Password?
                </a>
                <a href="register.php" id="register-link" class="text-sm text-gray-600 hover:text-gray-500">
                    Don't have an account? Sign Up
                </a>
            </div>

            <div>
                <button type="submit"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Login
                </button>
            </div>
        </form>

    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const userTab = document.getElementById('user-tab');
            const adminTab = document.getElementById('admin-tab');
            const loginTypeInput = document.getElementById('login_type');
            const registerLink = document.getElementById('register-link');
            const loginForm = document.getElementById('login-form');

            function setActiveTab(active, inactive) {
                // Set active tab styles
                active.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700');
                active.classList.add('border-indigo-600', 'text-indigo-700', 'hover:text-indigo-800');
                
                // Set inactive tab styles
                inactive.classList.remove('border-indigo-600', 'text-indigo-700', 'hover:text-indigo-800');
                inactive.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700');
            }

            // Set the initial active tab based on the PHP variable (important for maintaining state on login failure)
            const initialType = '<?php echo htmlspecialchars($login_type); ?>';
            if (initialType === 'admin') {
                setActiveTab(adminTab, userTab);
                registerLink.href = '#';
                registerLink.textContent = 'Admin Registration is Restricted';
            } else {
                setActiveTab(userTab, adminTab);
                registerLink.href = 'register.php';
                registerLink.textContent = "Don't have an account? Sign Up";
            }
            loginTypeInput.value = initialType;

            // --- Event Listeners ---
            
            userTab.addEventListener('click', () => {
                setActiveTab(userTab, adminTab);
                loginTypeInput.value = 'user';
                registerLink.href = 'register.php';
                registerLink.textContent = "Don't have an account? Sign Up";
            });

            adminTab.addEventListener('click', () => {
                setActiveTab(adminTab, userTab);
                loginTypeInput.value = 'admin';
                registerLink.href = '#'; // Admins don't register publicly
                registerLink.textContent = 'Admin Registration is Restricted';
            });
            
            // Simple Client-side Form Validation
            loginForm.addEventListener('submit', (e) => {
                const email = document.getElementById('email').value;
                const password = document.getElementById('password').value;
                
                if (email.trim() === '' || password.trim() === '') {
                    alert('Email and Password fields are required.');
                    e.preventDefault(); // Stop form submission
                }
            });
        });
    </script>
</body>
</html>