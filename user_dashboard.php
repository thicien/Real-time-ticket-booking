<?php
// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Authorization check: allow both 'user' and 'passenger' labels if either used by your AuthController
$valid_user_types = ['user', 'passenger'];
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], $valid_user_types, true)) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'] ?? null;
$user_name = isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : 'User';
const CURRENCY_SYMBOL = 'RWF ';

// Include the Bus model (adjust path if your structure differs)
require_once __DIR__ . '/models/Bus.php';
$busModel = new Bus();

// Locations datalist
$all_locations = [
    'Kigali', 'Musanze', 'Huye', 'Rubavu', 'Rusizi', 'Nyagatare',
    'Gicumbi', 'Rwamagana', 'Muhanga', 'Ngoma', 'Karongi', 'Nyanza',
    'Rulindo', 'Kayonza', 'Kirehe', 'Kamonyi', 'Gasabo', 'Kicukiro',
    'Nyamasheke', 'Gakenke', 'Burera', 'Ruhango', 'Bugesera', 'Nyarugenge'
];

// Fetch booking history; guard if method missing or returns non-array
$booking_history = [];
if (method_exists($busModel, 'getBookingHistory')) {
    $booking_history = $busModel->getBookingHistory($user_id);
    if (!is_array($booking_history)) {
        $booking_history = [];
    }
}

// Sort bookings: upcoming first, then newest first within each group
usort($booking_history, function($a, $b) {
    $now = time();
    $a_time = strtotime($a['departure_time'] ?? '1970-01-01 00:00:00');
    $b_time = strtotime($b['departure_time'] ?? '1970-01-01 00:00:00');

    $a_is_upcoming = $a_time > $now;
    $b_is_upcoming = $b_time > $now;

    if ($a_is_upcoming === $b_is_upcoming) {
        // newest first within the same group
        return $b_time <=> $a_time;
    }
    return $a_is_upcoming ? -1 : 1;
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
                <svg class="inline w-6 h-6 mr-2 text-primary-indigo" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                     xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                     d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
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
                            $departure_time = $booking['departure_time'] ?? '1970-01-01 00:00:00';
                            $is_upcoming = strtotime($departure_time) > time();
                            $status = $booking['status'] ?? 'Pending';
                            $status_color = $status === 'Confirmed' ? 'bg-success-green' : ($status === 'Cancelled' ? 'bg-danger-red' : 'bg-gray-400');
                            $border_color = $is_upcoming ? 'border-l-4 border-primary-indigo' : 'border-l-4 border-gray-300';
                        ?>
                        <div class="bg-white shadow-lg rounded-lg p-4 flex flex-col md:flex-row justify-between items-start md:items-center <?php echo $border_color; ?>">
                            
                            <div class="flex-grow mb-3 md:mb-0">
                                <p class="text-xs font-semibold uppercase text-gray-500">REF: <?php echo htmlspecialchars($booking['booking_id'] ?? 'N/A'); ?></p>
                                <h3 class="text-xl font-bold text-gray-800">
                                    <?php echo htmlspecialchars($booking['departure_location'] ?? 'Unknown'); ?> &rarr; <?php echo htmlspecialchars($booking['destination_location'] ?? 'Unknown'); ?>
                                </h3>
                                <p class="text-sm text-gray-600 mt-1">
                                    <strong>Operator:</strong> <?php echo htmlspecialchars($booking['bus_operator'] ?? '—'); ?> | 
                                    <strong>Seats:</strong> <?php echo htmlspecialchars($booking['seat_count'] ?? '1'); ?>
                                </p>
                            </div>

                            <div class="text-left md:text-right mr-6">
                                <p class="text-sm font-medium text-gray-700">
                                    <?php echo date('D, M j, Y', strtotime($departure_time)); ?>
                                </p>
                                <p class="text-xl font-extrabold text-primary-indigo">
                                    <?php echo date('H:i', strtotime($departure_time)); ?>
                                </p>
                            </div>
                            
                            <div class="flex flex-col items-start md:items-end space-y-2">
                                <span class="text-white text-xs font-bold px-3 py-1 rounded-full <?php echo $status_color; ?>">
                                    <?php echo htmlspecialchars($status); ?>
                                </span>
                                <p class="text-lg font-semibold text-gray-800">
                                    <?php echo CURRENCY_SYMBOL; ?><?php echo number_format($booking['total_amount'] ?? 0, 0); ?>
                                </p>
                                <a href="confirmation.php?booking_id=<?php echo urlencode($booking['booking_id'] ?? ''); ?>" 
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
