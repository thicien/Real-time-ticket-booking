<?php
// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 1. Authorization Check (ensure user is logged in)
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['user_type'] !== 'user') {
    header("Location: login.php");
    exit;
}

// 2. Include Controller
require_once 'controllers/BusController.php';

$user_name = htmlspecialchars($_SESSION['name']);
$search_results = [];
$error_message = '';

// 3. Get Search Parameters from URL (GET)
$departure = $_GET['from'] ?? '';
$destination = $_GET['to'] ?? '';
$date = $_GET['date'] ?? '';

// Format the date for display
$display_date = date('l, F j, Y', strtotime($date));

// 4. Perform Search using the Controller
if (!empty($departure) && !empty($destination) && !empty($date)) {
    
    $busController = new BusController();
    $results = $busController->getSearchResults($departure, $destination, $date);

    if ($results === false) {
        $error_message = "Search parameters are incomplete.";
    } elseif (empty($results)) {
        $error_message = "No buses found for this route on {$display_date}.";
    } else {
        $search_results = $results;
    }
} else {
    // If user accesses the page without search parameters
    $error_message = "Please return to the dashboard and enter your search criteria.";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - BusBook</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
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
                    <a href="user_dashboard.php" class="text-2xl font-bold text-primary-indigo">BusBook</a>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-700 font-medium"><?php echo $user_name; ?></span>
                    <a href="logout.php" class="text-red-600 hover:text-red-800 font-medium py-1 px-3 border border-red-600 rounded-md">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        
        <div class="bg-white shadow rounded-xl p-6 mb-8 border-l-4 border-primary-indigo">
            <h1 class="text-2xl font-bold text-gray-900">
                Buses from <?php echo htmlspecialchars($departure); ?> to <?php echo htmlspecialchars($destination); ?>
            </h1>
            <p class="text-gray-500 mt-1">
                Traveling on: <?php echo $display_date; ?> 
                <a href="user_dashboard.php" class="text-primary-indigo hover:underline ml-3 text-sm">Modify Search</a>
            </p>
        </div>

        <?php if ($error_message): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php elseif (!empty($search_results)): ?>
            
            <h2 class="text-xl font-semibold text-gray-800 mb-4">
                <?php echo count($search_results); ?> matching trips found:
            </h2>
            
            <div class="space-y-4">
                <?php foreach ($search_results as $bus): ?>
                    <?php
                        // Format times and amenities
                        $departure_time = date('H:i', strtotime($bus['departure_time']));
                        $arrival_time = date('H:i', strtotime($bus['arrival_time']));
                        // Note: total_seats is currently not dynamic. We'll improve this later.
                        $available_seats = $bus['total_seats']; 
                        $amenities = explode(',', $bus['amenities']); // Assuming amenities is a comma-separated string
                    ?>
                    
                    <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-200 flex flex-col md:flex-row justify-between items-start md:items-center hover:shadow-xl transition duration-300">
                        
                        <div class="mb-4 md:mb-0 md:w-1/3">
                            <p class="text-lg font-bold text-gray-900"><?php echo htmlspecialchars($bus['bus_operator']); ?></p>
                            <p class="text-sm text-gray-500"><?php echo htmlspecialchars($bus['bus_type']); ?> | Route: <?php echo htmlspecialchars($bus['route_name']); ?></p>
                            <p class="text-sm text-green-600 mt-1">
                                <?php echo htmlspecialchars($available_seats); ?> Seats Available
                            </p>
                        </div>
                        
                        <div class="mb-4 md:mb-0 md:w-1/3 flex justify-center space-x-6">
                            <div>
                                <p class="text-2xl font-bold text-primary-indigo"><?php echo $departure_time; ?></p>
                                <p class="text-xs text-gray-500">Departure</p>
                            </div>
                            <div class="text-center text-gray-400 self-center">
                                <span class="text-sm">--&gt;</span>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-gray-900"><?php echo $arrival_time; ?></p>
                                <p class="text-xs text-gray-500">Arrival</p>
                            </div>
                        </div>

                        <div class="md:w-1/3 flex justify-end items-center space-x-4">
                            <div class="text-right">
                                <p class="text-3xl font-extrabold text-green-600">
                                    $<?php echo number_format($bus['price'], 0); ?>
                                </p>
                                <p class="text-xs text-gray-500">per seat</p>
                            </div>
                            <a href="seat_selection.php?schedule_id=<?php echo $bus['schedule_id']; ?>" 
                               class="bg-primary-indigo hover:bg-indigo-700 text-white font-semibold py-3 px-6 rounded-lg shadow-md transition duration-150">
                                Book Seat
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
        <?php else: ?>
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative" role="alert">
                Start your search from the <a href="user_dashboard.php" class="font-bold underline">dashboard</a>.
            </div>
        <?php endif; ?>

    </main>
    
</body>
</html>