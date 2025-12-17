<?php
// search_results.php

// Start session and authorization check
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$valid_user_types = ['user', 'passenger'];
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], $valid_user_types, true)) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/models/Bus.php';
$busModel = new Bus();
const CURRENCY_SYMBOL = 'RWF ';

$from = trim(htmlspecialchars($_GET['from'] ?? ''));
$to = trim(htmlspecialchars($_GET['to'] ?? ''));
$date = htmlspecialchars($_GET['date'] ?? date('Y-m-d'));

if (empty($from) || empty($to) || empty($date)) {
    $_SESSION['error_message'] = "Please provide departure, destination, and date to search for buses.";
    header("Location: user_dashboard.php");
    exit;
}

$available_schedules = [];
if (method_exists($busModel, 'searchSchedules')) {
    $available_schedules = $busModel->searchSchedules($from, $to, $date);
}

$display_date = date('l, F j, Y', strtotime($date));

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results: <?php echo $from; ?> to <?php echo $to; ?></title>
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
                    <a href="user_dashboard.php" class="text-gray-600 hover:text-primary-indigo font-medium">Dashboard</a>
                    <a href="logout.php" class="text-red-600 hover:text-red-800 font-medium py-1 px-3 border border-red-600 rounded-md">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        
        <h1 class="text-3xl font-extrabold text-gray-900 mb-2">Bus Search Results</h1>
        <p class="text-xl text-gray-600 mb-8">
            <span class="font-bold text-primary-indigo"><?php echo $from; ?></span> &rarr; <span class="font-bold text-primary-indigo"><?php echo $to; ?></span> on <?php echo $display_date; ?>
        </p>

        <a href="user_dashboard.php" class="inline-flex items-center text-sm text-primary-indigo hover:text-indigo-700 mb-6">
            &larr; Change Search Criteria
        </a>

        <?php if (empty($available_schedules)): ?>
            <div class="text-center py-16 bg-white shadow-xl rounded-xl border border-dashed border-gray-300">
                <p class="text-2xl text-gray-700 font-semibold mb-3">No Buses Found</p>
                <p class="text-lg text-gray-500">
                    We couldn't find any scheduled buses for the selected route and date.
                </p>
            </div>
        <?php else: ?>
            <div class="space-y-6">
                <?php foreach ($available_schedules as $schedule): ?>
                    <?php
                    // Display calculation: remaining seats
                    $total_seats = $schedule['bus_capacity'] ?? 0;
                    $booked_seats = $schedule['booked_seats'] ?? 0;
                    $remaining_seats = $total_seats - $booked_seats;
                    ?>
                    <div class="bg-white shadow-lg rounded-xl p-6 flex flex-col md:flex-row justify-between items-start md:items-center border-l-4 border-primary-indigo">
                        
                        <div class="flex-grow mb-4 md:mb-0">
                            <h3 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($schedule['operator_name'] ?? 'Unknown Operator'); ?></h3>
                            <p class="text-sm text-gray-600 mt-1">
                                <span class="font-semibold">Route:</span> <?php echo htmlspecialchars($schedule['departure_location'] ?? 'N/A'); ?> &rarr; <?php echo htmlspecialchars($schedule['destination_location'] ?? 'N/A'); ?>
                            </p>
                            <p class="text-sm text-gray-600">
                                <strong>Bus Plate:</strong> <?php echo htmlspecialchars($schedule['plate_number'] ?? 'N/A'); ?> 
                                </p>
                        </div>

                        <div class="text-left md:text-right mr-6">
                            <p class="text-sm font-medium text-gray-700">Departure Time</p>
                            <p class="text-3xl font-extrabold text-primary-indigo">
                                <?php echo date('H:i', strtotime($schedule['departure_time'] ?? '00:00')); ?>
                            </p>
                        </div>
                        
                        <div class="flex flex-col items-start md:items-end space-y-2">
                            <span class="text-xl font-semibold text-gray-800">
                                <?php echo CURRENCY_SYMBOL; ?><?php echo number_format($schedule['price'] ?? 0, 0); ?>
                            </span>
                            <span class="text-sm font-medium <?php echo ($remaining_seats <= 5 && $remaining_seats > 0) ? 'text-danger-red' : 'text-success-green'; ?>">
                                <?php echo $remaining_seats; ?> Seats Remaining
                            </span>
                            
                            <a href="book_seat.php?schedule_id=<?php echo urlencode($schedule['schedule_id'] ?? ''); ?>" 
                               class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition duration-150 text-sm">
                                Select Seats &rarr;
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </main>
    
</body>
</html>