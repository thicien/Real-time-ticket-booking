<?php
// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in as a 'user' (passenger)
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['user_type'] !== 'user') {
    // If not logged in or wrong user type, redirect to login page
    header("Location: login.php");
    exit;
}

$user_name = htmlspecialchars($_SESSION['name']);

// NOTE: No POST handling is needed here. The form now submits directly to search_results.php via GET.
// The search form handling logic has been moved entirely to search_results.php
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Search Buses</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Set the Tailwind configuration for easy customization
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary-indigo': '#4f46e5',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100 min-h-screen">
    
    <nav class="bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <span class="text-2xl font-bold text-primary-indigo">BusBook</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="#" class="text-gray-600 hover:text-primary-indigo font-medium">Bookings</a>
                    <a href="#" class="text-gray-600 hover:text-primary-indigo font-medium">Profile</a>
                    <span class="text-gray-500">|</span>
                    <span class="text-gray-700 font-medium"><?php echo $user_name; ?></span>
                    <a href="logout.php" class="text-red-600 hover:text-red-800 font-medium py-1 px-3 border border-red-600 rounded-md">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        
        <h1 class="text-3xl font-extrabold text-gray-900 mb-8">Hello, <?php echo $user_name; ?>! Let's book a trip.</h1>

        <div class="bg-white shadow-xl rounded-xl p-6 md:p-10 mb-10 border border-primary-indigo/20">
            <h2 class="text-2xl font-semibold text-gray-800 mb-6 border-b pb-3">Find Your Bus</h2>
            
            <form action="search_results.php" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
                <div>
                    <label for="departure" class="block text-sm font-medium text-gray-700 mb-1">Departure Location</label>
                    <input type="text" id="departure" name="from" required list="departure_cities"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-primary-indigo focus:border-primary-indigo"
                           placeholder="e.g., Kigali">
                    <datalist id="departure_cities">
                        <option value="Kigali">
                        <option value="Musanze">
                        <option value="Huye">
                    </datalist>
                </div>

                <div>
                    <label for="destination" class="block text-sm font-medium text-gray-700 mb-1">Destination Location</label>
                    <input type="text" id="destination" name="to" required list="destination_cities"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-primary-indigo focus:border-primary-indigo"
                           placeholder="e.g., Rubavu">
                    <datalist id="destination_cities">
                        <option value="Rubavu">
                        <option value="Kigali">
                        <option value="Kayonza">
                    </datalist>
                </div>

                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Date of Travel</label>
                    <input type="date" id="date" name="date" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-primary-indigo focus:border-primary-indigo">
                </div>

                <div class="md:col-span-1">
                    <button type="submit"
                            class="w-full bg-primary-indigo hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition duration-150">
                        Search Buses
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white shadow-md rounded-xl p-8">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Your Summary</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <a href="#" class="block bg-gray-50 hover:bg-gray-100 p-6 rounded-lg border border-gray-200 transition duration-150">
                    <h3 class="text-xl font-semibold text-gray-800 mb-1">Booking History</h3>
                    <p class="text-gray-500">View your past and upcoming travel reservations.</p>
                </a>
                <a href="#" class="block bg-gray-50 hover:bg-gray-100 p-6 rounded-lg border border-gray-200 transition duration-150">
                    <h3 class="text-xl font-semibold text-gray-800 mb-1">Manage Profile</h3>
                    <p class="text-gray-500">Update your contact details and password.</p>
                </a>
            </div>
        </div>

    </main>
    
</body>
</html>