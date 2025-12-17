<?php
// reports.php

// 1. SESSION AND SECURITY CHECK
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Security Check: Ensure user is logged in AND is of type 'admin'
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// 2. INCLUDES AND INITIALIZATION
require_once 'models/Report.php'; 

$reportModel = new Report();
if (!defined('CURRENCY_SYMBOL')) {
    define('CURRENCY_SYMBOL', 'RWF ');
}

$booking_summary = $reportModel->getBookingStatusSummary();

$today = date('Y-m-d');
$last_30_days = date('Y-m-d', strtotime('-30 days'));

$start_date = $_GET['start_date'] ?? $last_30_days;
$end_date = $_GET['end_date'] ?? $today;

if (!strtotime($start_date) || !strtotime($end_date)) {
    $start_date = $last_30_days;
    $end_date = $today;
}

$revenue_data = $reportModel->getRevenueReport($start_date, $end_date);

$occupancy_data = $reportModel->getOccupancyReport();

$admin_name = htmlspecialchars($_SESSION['name'] ?? 'Administrator');
$nav_items = [
    'Bus Management' => 'bus_management.php',
    'Route Management' => 'route_management.php',
    'Schedule Management' => 'schedule_management.php',
    'Booking Management' => 'booking_management.php',
    'Payments Management' => 'payments_management.php',
    'Driver/Staff Management' => 'staff_management.php',
    'Reports' => 'reports.php',
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Reports - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .report-card { transition: transform 0.2s; }
        .report-card:hover { transform: translateY(-3px); }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex">
    
    <div class="w-64 bg-gray-800 text-white flex flex-col p-4 shadow-2xl">
        <h2 class="text-2xl font-bold mb-8 border-b border-gray-700 pb-4 text-indigo-400">Admin Panel</h2>
        <nav class="flex-grow">
            <ul class="space-y-2">
                <li><a href="admin_dashboard.php" class="flex items-center p-2 rounded-lg hover:bg-gray-700 transition duration-150">Dashboard</a></li>
                <?php foreach ($nav_items as $label => $file): ?>
                    <li>
                        <a href="<?php echo htmlspecialchars($file); ?>" class="flex items-center p-2 rounded-lg hover:bg-gray-700 transition duration-150 
                            <?php echo ($file == 'reports.php') ? 'bg-indigo-600 font-semibold' : ''; ?>">
                            <?php echo htmlspecialchars($label); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
        <div class="mt-auto pt-4 border-t border-gray-700">
            <p class="text-sm mb-2">Logged in as: <span class="font-semibold"><?php echo $admin_name; ?></span></p>
            <a href="logout.php" class="flex items-center p-2 rounded-lg text-red-400 hover:bg-gray-700 transition duration-150">Logout</a>
        </div>
    </div>
    
    <div class="flex-grow p-8">
        <h1 class="text-4xl font-extrabold text-gray-900 mb-8">System Reports</h1>

        <div class="bg-white p-6 rounded-xl shadow-lg mb-8">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Revenue Report Filter</h2>
            <form method="GET" action="reports.php" class="flex flex-col md:flex-row gap-4 items-end">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                    <input type="date" name="start_date" id="start_date" required
                           value="<?php echo htmlspecialchars($start_date); ?>"
                           class="mt-1 block border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                    <input type="date" name="end_date" id="end_date" required
                           value="<?php echo htmlspecialchars($end_date); ?>"
                           class="mt-1 block border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <button type="submit" class="w-full md:w-auto py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Apply Filter
                </button>
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            
            <div class="report-card bg-green-500 text-white p-6 rounded-xl shadow-md">
                <p class="text-sm font-medium uppercase">Total Revenue (Paid)</p>
                <p class="text-4xl font-extrabold mt-1"><?php echo CURRENCY_SYMBOL . number_format($revenue_data['total_revenue'] ?? 0, 0); ?></p>
                <p class="text-sm mt-2">from <?php echo htmlspecialchars($start_date); ?> to <?php echo htmlspecialchars($end_date); ?></p>
            </div>

            <div class="report-card bg-indigo-500 text-white p-6 rounded-xl shadow-md">
                <p class="text-sm font-medium uppercase">Total Bookings</p>
                <p class="text-4xl font-extrabold mt-1"><?php echo number_format($booking_summary['Total']); ?></p>
                <p class="text-sm mt-2">All-time record count</p>
            </div>

            <div class="report-card bg-yellow-500 text-white p-6 rounded-xl shadow-md">
                <p class="text-sm font-medium uppercase">Confirmed</p>
                <p class="text-4xl font-extrabold mt-1"><?php echo number_format($booking_summary['Confirmed']); ?></p>
                <p class="text-sm mt-2">Pending: <?php echo number_format($booking_summary['Pending']); ?></p>
            </div>

            <div class="report-card bg-blue-500 text-white p-6 rounded-xl shadow-md">
                <p class="text-sm font-medium uppercase">Total Paid Transactions</p>
                <p class="text-4xl font-extrabold mt-1"><?php echo number_format($revenue_data['total_transactions'] ?? 0); ?></p>
                <p class="text-sm mt-2">Within filtered date range</p>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-lg">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2">Future Schedule Occupancy</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Schedule ID / Route</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Departure Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bus Capacity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Confirmed Seats</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Occupancy Rate</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($occupancy_data)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No future schedules found for occupancy reporting.</td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach ($occupancy_data as $schedule): 
                            $occupied = (int)$schedule['occupied_seats'];
                            $capacity = (int)$schedule['bus_capacity'];
                            $occupancy_rate = ($capacity > 0) ? round(($occupied / $capacity) * 100) : 0;
                            $rate_class = '';
                            if ($occupancy_rate >= 80) $rate_class = 'bg-red-100 text-red-800';
                            elseif ($occupancy_rate >= 50) $rate_class = 'bg-yellow-100 text-yellow-800';
                            else $rate_class = 'bg-green-100 text-green-800';
                        ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="text-gray-900 font-semibold">Sch: #<?php echo htmlspecialchars($schedule['schedule_id']); ?></div>
                                    <div class="text-indigo-600 text-xs"><?php echo htmlspecialchars($schedule['route_name']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <?php echo date('M d, Y h:i A', strtotime($schedule['departure_time'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo htmlspecialchars($capacity); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-semibold">
                                    <?php echo htmlspecialchars($occupied); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <span class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-medium <?php echo $rate_class; ?>">
                                        <?php echo $occupancy_rate; ?>%
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</body>
</html>