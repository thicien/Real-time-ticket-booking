<?php
// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 1. Authorization Check
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['user_type'] !== 'user') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = htmlspecialchars($_SESSION['name']);
const CURRENCY_SYMBOL = 'RWF ';

// 2. Data/Model Inclusion
require_once 'models/Bus.php';
$busModel = new Bus();

// 3. Define Locations (20+ common locations in Rwanda for Datalist)
$all_locations = [
    'Kigali', 'Musanze', 'Huye', 'Rubavu', 'Rusizi', 'Nyagatare', 
    'Gicumbi', 'Rwamagana', 'Muhanga', 'Ngoma', 'Karongi', 'Nyanza', 
    'Rulindo', 'Kayonza', 'Kirehe', 'Kamonyi', 'Gasabo', 'Kicukiro', 
    'Nyamasheke', 'Gakenke', 'Burera', 'Ruhango', 'Bugesera', 'Nyarugenge'
];

// 4. Fetch Booking History (CRITICAL: You must implement getBookingHistory method in Bus.php)
/*
    The getBookingHistory method in your Bus.php model MUST return an array like this:
    [
        [
            'booking_id' => 101, 
            'departure_location' => 'Kigali', 
            'destination_location' => 'Musanze', 
            'departure_time' => '2025-12-20 08:00:00',
            'bus_operator' => 'Volcano Express',
            'seat_count' => 2,
            'total_amount' => 5000,
            'status' => 'Confirmed'
        ],
        // ... more bookings
    ]
*/
$booking_history = $busModel->getBookingHistory($user_id);

// Sort bookings: Upcoming first, then past.
usort($booking_history, function($a, $b) {
    $now = time();
    $a_time = strtotime($a['departure_time']);
    $b_time = strtotime($b['departure_time']);

    $a_is_upcoming = $a_time > $now;
    $b_is_upcoming = $b_time > $now;

    if ($a_is_upcoming === $b_is_upcoming) {
        return $b_time - $a_time; // Sort newest first within the group
    }
    return $a_is_upcoming ? -1 : 1; // Put upcoming first
});


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Search Buses</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary-indigo': '#4f46e5',
                        'success-green': '#10b981',
                        'danger-red': '#ef4444',
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
                    <a href="#booking-history" class="text-primary-indigo font-bold border-b-2 border-primary-indigo">Bookings</a>
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
                        <?php foreach ($all_locations as $city): ?>
                            <option value="<?php echo htmlspecialchars($city); ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>

                <div>
                    <label for="destination" class="block text-sm font-medium text-gray-700 mb-1">Destination Location</label>
                    <input type="text" id="destination" name="to" required list="destination_cities"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-primary-indigo focus:border-primary-indigo"
                           placeholder="e.g., Rubavu">
                    <datalist id="destination_cities">
                        <?php foreach ($all_locations as $city): ?>
                            <option value="<?php echo htmlspecialchars($city); ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>

                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Date of Travel</label>
                    <input type="date" id="date" name="date" required
                           min="<?php echo date('Y-m-d'); ?>" 
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

        <div id="booking-history" class="bg-white shadow-md rounded-xl p-6 md:p-8">
            <h2 class="text-2xl font-semibold text-gray-800 mb-6 border-b pb-3">
                <svg class="inline w-6 h-6 mr-2 text-primary-indigo" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                Your Booking History (<?php echo count($booking_history); ?> Trips)
            </h2>
            
            <?php if (empty($booking_history)): ?>
                <div class="text-center py-10 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                    <p class="text-lg text-gray-600 font-medium">You have no recorded bookings yet.</p>
                    <p class="text-gray-500 mt-2">Start by searching for a route above!</p>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($booking_history as $booking): ?>
                        <?php 
                            $is_upcoming = strtotime($booking['departure_time']) > time();
                            $status_color = $booking['status'] === 'Confirmed' ? 'bg-success-green' : ($booking['status'] === 'Cancelled' ? 'bg-danger-red' : 'bg-gray-400');
                            $border_color = $is_upcoming ? 'border-l-4 border-primary-indigo' : 'border-l-4 border-gray-300';
                        ?>
                        <div class="bg-white shadow-lg rounded-lg p-4 flex flex-col md:flex-row justify-between items-start md:items-center <?php echo $border_color; ?>">
                            
                            <div class="flex-grow mb-3 md:mb-0">
                                <p class="text-xs font-semibold uppercase text-gray-500">REF: <?php echo htmlspecialchars($booking['booking_id']); ?></p>
                                <h3 class="text-xl font-bold text-gray-800">
                                    <?php echo htmlspecialchars($booking['departure_location']); ?> &rarr; <?php echo htmlspecialchars($booking['destination_location']); ?>
                                </h3>
                                <p class="text-sm text-gray-600 mt-1">
                                    **Operator:** <?php echo htmlspecialchars($booking['bus_operator']); ?> | 
                                    **Seats:** <?php echo htmlspecialchars($booking['seat_count']); ?>
                                </p>
                            </div>

                            <div class="text-left md:text-right mr-6">
                                <p class="text-sm font-medium text-gray-700">
                                    <?php echo date('D, M j, Y', strtotime($booking['departure_time'])); ?>
                                </p>
                                <p class="text-xl font-extrabold text-primary-indigo">
                                    <?php echo date('H:i', strtotime($booking['departure_time'])); ?>
                                </p>
                            </div>
                            
                            <div class="flex flex-col items-start md:items-end space-y-2">
                                <span class="text-white text-xs font-bold px-3 py-1 rounded-full <?php echo $status_color; ?>">
                                    <?php echo htmlspecialchars($booking['status']); ?>
                                </span>
                                <p class="text-lg font-semibold text-gray-800">
                                    <?php echo CURRENCY_SYMBOL; ?><?php echo number_format($booking['total_amount'], 0); ?>
                                </p>
                                <a href="confirmation.php?booking_id=<?php echo $booking['booking_id']; ?>" 
                                   class="text-sm font-medium text-primary-indigo hover:text-indigo-700 underline">
                                    View E-Ticket &rarr;
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </main>
    
</body>
</html>