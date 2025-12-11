<?php
// booking_management.php

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
// Requires the dedicated Admin Booking Controller
require_once 'controllers/BookingManagementController.php'; 

$bookingController = new BookingManagementController();
$message = '';
$message_type = ''; 
if (!defined('CURRENCY_SYMBOL')) {
    define('CURRENCY_SYMBOL', 'RWF ');
}


// 3. HANDLE POST REQUESTS (UPDATE STATUS)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $post_action = $_POST['action'] ?? '';
    $booking_id = $_POST['booking_id'] ?? null;
    
    if ($booking_id && in_array($post_action, ['confirm', 'cancel'])) {
        $new_status = ($post_action == 'confirm') ? 'Confirmed' : 'Cancelled';
        $result = $bookingController->updateBookingStatus($booking_id, $new_status);
    } else {
        $result = ['success' => false, 'message' => 'Invalid action or missing Booking ID.'];
    }

    $message = $result['message'];
    $message_type = $result['success'] ? 'success' : 'error';
    
    // Redirect on success/failure to clear POST data and show message
    header("Location: booking_management.php?msg=" . urlencode($message) . "&type=" . $message_type);
    exit;
}

// 4. HANDLE GET REQUESTS (MESSAGES)
if (isset($_GET['msg']) && isset($_GET['type'])) {
    $message = htmlspecialchars($_GET['msg']);
    $message_type = htmlspecialchars($_GET['type']);
}


// 5. FETCH ALL BOOKINGS FOR THE LIST VIEW
$bookings = $bookingController->index(); 

// 6. Define Nav Items for Sidebar
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
    <title>Booking Management - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Table styles for better readability */
        .status-pending { background-color: #fffbe6; color: #92400e; border: 1px solid #fcd34d; }
        .status-confirmed { background-color: #f0fdf4; color: #15803d; border: 1px solid #4ade80; }
        .status-cancelled { background-color: #fef2f2; color: #b91c1c; border: 1px solid #f87171; }
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
                            <?php echo ($file == 'booking_management.php') ? 'bg-indigo-600 font-semibold' : ''; ?>">
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
        <h1 class="text-4xl font-extrabold text-gray-900 mb-6">Booking Management</h1>
        
        <?php if ($message): ?>
            <div class="p-4 mb-4 rounded-lg <?php echo ($message_type == 'success') ? 'bg-green-100 text-green-700 border border-green-400' : 'bg-red-100 text-red-700 border border-red-400'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="bg-white p-6 rounded-xl shadow-lg">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2">All Passenger Bookings</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID / Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Passenger / Seat</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Route / Bus</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Departure Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Booking Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($bookings)): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">No bookings found in the system.</td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach ($bookings as $booking): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="text-gray-900 font-semibold">#<?php echo htmlspecialchars($booking['booking_id']); ?></div>
                                    <div class="text-gray-500 text-xs"><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="text-gray-900 font-medium" title="<?php echo htmlspecialchars($booking['passenger_email']); ?>"><?php echo htmlspecialchars($booking['passenger_name']); ?></div>
                                    <div class="text-indigo-600 font-bold">Seat: <?php echo htmlspecialchars($booking['seat_number']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="text-gray-700"><?php echo htmlspecialchars($booking['route_name']); ?></div>
                                    <div class="text-gray-500 text-xs" title="Bus: <?php echo htmlspecialchars($booking['bus_reg']); ?> | Capacity: <?php echo htmlspecialchars($booking['bus_capacity']); ?>"><?php echo htmlspecialchars($booking['bus_capacity']) . ' Seater'; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('M d, Y h:i A', strtotime($booking['departure_time'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-medium 
                                        <?php 
                                            $p_status = htmlspecialchars($booking['payment_status'] ?? 'Pending');
                                            if ($p_status == 'Paid') echo 'bg-green-100 text-green-800';
                                            elseif ($p_status == 'Failed') echo 'bg-red-100 text-red-800';
                                            else echo 'bg-yellow-100 text-yellow-800';
                                        ?>">
                                        <?php echo $p_status; ?>
                                    </span>
                                    <div class="text-gray-900 font-semibold text-xs mt-1"><?php echo CURRENCY_SYMBOL . number_format($booking['fare_base'], 0); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-medium 
                                        <?php 
                                            $b_status = htmlspecialchars($booking['booking_status']);
                                            if ($b_status == 'Confirmed') echo 'bg-green-50 text-green-700 border border-green-300';
                                            elseif ($b_status == 'Cancelled') echo 'bg-red-50 text-red-700 border border-red-300';
                                            else echo 'bg-yellow-50 text-yellow-700 border border-yellow-300';
                                        ?>">
                                        <?php echo $b_status; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                    <?php if ($booking['booking_status'] == 'Pending'): ?>
                                        <form method="POST" action="booking_management.php" class="inline">
                                            <input type="hidden" name="action" value="confirm">
                                            <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($booking['booking_id']); ?>">
                                            <button type="submit" onclick="return confirm('Confirm Booking #<?php echo htmlspecialchars($booking['booking_id']); ?>?')"
                                                    class="text-green-600 hover:text-green-900 bg-green-100 p-1 rounded-md text-xs font-semibold">Confirm</button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <?php if ($booking['booking_status'] != 'Cancelled'): ?>
                                        <form method="POST" action="booking_management.php" class="inline">
                                            <input type="hidden" name="action" value="cancel">
                                            <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($booking['booking_id']); ?>">
                                            <button type="submit" onclick="return confirm('Are you sure you want to CANCEL Booking #<?php echo htmlspecialchars($booking['booking_id']); ?>?')"
                                                    class="text-red-600 hover:text-red-900 bg-red-100 p-1 rounded-md text-xs font-semibold">Cancel</button>
                                        </form>
                                    <?php endif; ?>
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