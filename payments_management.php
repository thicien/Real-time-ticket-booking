<?php
// payments_management.php

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
require_once 'controllers/PaymentManagementController.php'; 

$paymentController = new PaymentManagementController();
$message = '';
$message_type = ''; 
if (!defined('CURRENCY_SYMBOL')) {
    define('CURRENCY_SYMBOL', 'RWF ');
}

// Define the payment statuses for the dropdown and validation
$payment_statuses = ['Pending', 'Paid', 'Failed', 'Refunded'];


// 3. HANDLE POST REQUESTS (UPDATE STATUS)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $post_action = $_POST['action'] ?? '';
    $payment_id = $_POST['payment_id'] ?? null;
    $new_status = $_POST['new_status'] ?? '';
    
    if ($payment_id && $post_action == 'update_status' && in_array($new_status, $payment_statuses)) {
        $result = $paymentController->updatePaymentStatus($payment_id, $new_status);
    } else {
        $result = ['success' => false, 'message' => 'Invalid action, missing ID, or invalid status value.'];
    }

    $message = $result['message'];
    $message_type = $result['success'] ? 'success' : 'error';
    
    // Redirect on success/failure to clear POST data and show message
    header("Location: payments_management.php?msg=" . urlencode($message) . "&type=" . $message_type);
    exit;
}

// 4. HANDLE GET REQUESTS (MESSAGES)
if (isset($_GET['msg']) && isset($_GET['type'])) {
    $message = htmlspecialchars($_GET['msg']);
    $message_type = htmlspecialchars($_GET['type']);
}

$payments = $paymentController->index(); 

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
    <title>Payments Management - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .status-paid { background-color: #f0fdf4; color: #15803d; border: 1px solid #4ade80; }
        .status-pending { background-color: #fffbe6; color: #92400e; border: 1px solid #fcd34d; }
        .status-failed, .status-refunded { background-color: #fef2f2; color: #b91c1c; border: 1px solid #f87171; }
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
                            <?php echo ($file == 'payments_management.php') ? 'bg-indigo-600 font-semibold' : ''; ?>">
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
        <h1 class="text-4xl font-extrabold text-gray-900 mb-6">Payments Management</h1>
        
        <?php if ($message): ?>
            <div class="p-4 mb-4 rounded-lg <?php echo ($message_type == 'success') ? 'bg-green-100 text-green-700 border border-green-400' : 'bg-red-100 text-red-700 border border-red-400'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="bg-white p-6 rounded-xl shadow-lg">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2">All Transactions</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment ID / Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount / Method</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Booking Info</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Passenger</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaction Ref</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($payments)): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">No payment records found.</td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach ($payments as $payment): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="text-gray-900 font-semibold">#<?php echo htmlspecialchars($payment['payment_id']); ?></div>
                                    <div class="text-gray-500 text-xs"><?php echo date('M d, Y h:i A', strtotime($payment['payment_date'])); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="text-green-600 font-bold"><?php echo CURRENCY_SYMBOL . number_format($payment['payment_amount'], 0); ?></div>
                                    <div class="text-gray-500 text-xs mt-1"><?php echo htmlspecialchars($payment['payment_method']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-medium 
                                        <?php 
                                            $p_status = htmlspecialchars($payment['payment_status']);
                                            if ($p_status == 'Paid') echo 'bg-green-100 text-green-800';
                                            elseif ($p_status == 'Pending') echo 'bg-yellow-100 text-yellow-800';
                                            else echo 'bg-red-100 text-red-800';
                                        ?>">
                                        <?php echo $p_status; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="text-gray-900 font-medium">Booking: #<?php echo htmlspecialchars($payment['booking_id']); ?> (Seat <?php echo htmlspecialchars($payment['seat_number']); ?>)</div>
                                    <div class="text-gray-500 text-xs">Route: <?php echo htmlspecialchars($payment['route_name']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="text-gray-900 font-medium"><?php echo htmlspecialchars($payment['passenger_name']); ?></div>
                                    <div class="text-gray-500 text-xs" title="<?php echo htmlspecialchars($payment['passenger_email']); ?>"><?php echo htmlspecialchars($payment['passenger_phone']); ?></div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 truncate max-w-xs">
                                    <?php echo htmlspecialchars($payment['transaction_reference'] ?? 'N/A'); ?>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium">
                                    <form method="POST" action="payments_management.php" class="flex items-center space-x-2">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="payment_id" value="<?php echo htmlspecialchars($payment['payment_id']); ?>">
                                        <select name="new_status" required class="border border-gray-300 rounded-md text-xs py-1 px-2 focus:ring-indigo-500 focus:border-indigo-500">
                                            <?php foreach ($payment_statuses as $status): ?>
                                                <option value="<?php echo htmlspecialchars($status); ?>" 
                                                    <?php echo ($payment['payment_status'] == $status) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($status); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" onclick="return confirm('Manually change status for Payment ID <?php echo htmlspecialchars($payment['payment_id']); ?>?')"
                                                class="bg-indigo-500 hover:bg-indigo-600 text-white text-xs py-1 px-2 rounded-md transition duration-150">Update</button>
                                    </form>
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