<?php
// bus_management.php

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
// Ensure this path is correct for your BusController
require_once 'controllers/BusController.php'; 

$busController = new BusController();
$message = '';
$message_type = ''; // 'success' or 'error'

// Initialize bus data for the form (used for update/edit mode)
$edit_bus = null;
$action = $_GET['action'] ?? '';
$bus_id = $_GET['id'] ?? null;


// 3. HANDLE POST REQUESTS (CREATE, UPDATE, DELETE)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $post_action = $_POST['action'] ?? '';
    
    // NOTE: The BusController::createBus and BusController::updateBus MUST be ready to receive 
    // the new fields: rows, columns, amenities.
    
    if ($post_action == 'create') {
        $result = $busController->createBus($_POST);
    } elseif ($post_action == 'update') {
        $result = $busController->updateBus($_POST);
    } elseif ($post_action == 'delete') {
        // Ensure bus_id is set for delete
        $result = $busController->deleteBus($_POST['bus_id'] ?? null);
    } else {
        $result = ['success' => false, 'message' => 'Invalid action specified.'];
    }

    $message = $result['message'];
    $message_type = $result['success'] ? 'success' : 'error';
    
    // Redirect to clear POST data and show message
    if ($result['success']) {
        // Simple redirect to the list page after success
        header("Location: bus_management.php?msg=" . urlencode($message) . "&type=" . $message_type);
        exit;
    }
}

if (isset($_GET['msg']) && isset($_GET['type'])) {
    $message = htmlspecialchars($_GET['msg']);
    $message_type = htmlspecialchars($_GET['type']);
}

if ($action == 'edit' && $bus_id) {

    $edit_bus = $busController->getBusById($bus_id); 
    if (!$edit_bus) {
        $message = "Bus record not found.";
        $message_type = 'error';
        $action = ''; 
    }
}

$buses = $busController->index(); 

$admin_name = htmlspecialchars($_SESSION['name'] ?? 'Administrator');
$nav_items = [
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
    <title>Bus Management - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .bg-primary-indigo { background-color: #4f46e5; }
        .text-primary-indigo { color: #4f46e5; }
        .border-primary-indigo { border-color: #4f46e5; }
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
                            <?php echo ($file == 'bus_management.php') ? 'bg-indigo-600 font-semibold' : ''; ?>">
                            <?php echo htmlspecialchars($label); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
                 <li>
                    <a href="bus_management.php" class="flex items-center p-2 rounded-lg bg-indigo-600 font-semibold transition duration-150">
                        Bus Management
                    </a>
                </li>
            </ul>
        </nav>
        <div class="mt-auto pt-4 border-t border-gray-700">
            <p class="text-sm mb-2">Logged in as: <span class="font-semibold"><?php echo $admin_name; ?></span></p>
            <a href="logout.php" class="flex items-center p-2 rounded-lg text-red-400 hover:bg-gray-700 transition duration-150">Logout</a>
        </div>
    </div>
    
    <div class="flex-grow p-8">
        <h1 class="text-4xl font-extrabold text-gray-900 mb-6">Bus Management</h1>
        
        <?php if ($message): ?>
            <div class="p-4 mb-4 rounded-lg <?php echo ($message_type == 'success') ? 'bg-green-100 text-green-700 border border-green-400' : 'bg-red-100 text-red-700 border border-red-400'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="bg-white p-6 rounded-xl shadow-lg mb-8">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2">
                <?php echo $edit_bus ? 'Edit Bus: ' . htmlspecialchars($edit_bus['bus_plate']) : 'Add New Bus'; ?>
            </h2>
            
            <form method="POST" action="bus_management.php" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <input type="hidden" name="action" value="<?php echo $edit_bus ? 'update' : 'create'; ?>">
                <?php if ($edit_bus): ?>
                    <input type="hidden" name="bus_id" value="<?php echo htmlspecialchars($edit_bus['bus_id']); ?>">
                <?php endif; ?>

                <div>
                    <label for="registration_number" class="block text-sm font-medium text-gray-700">Bus Plate / Registration No.</label>
                    <input type="text" name="registration_number" id="registration_number" required
                           value="<?php echo htmlspecialchars($edit_bus['bus_plate'] ?? ''); ?>"
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                
                <div>
                    <label for="capacity" class="block text-sm font-medium text-gray-700">Total Seats (Capacity)</label>
                    <input type="number" name="capacity" id="capacity" required min="1" max="100"
                           value="<?php echo htmlspecialchars($edit_bus['total_seats'] ?? ''); ?>"
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label for="model" class="block text-sm font-medium text-gray-700">Bus Type / Model</label>
                    <input type="text" name="model" id="model" required
                           value="<?php echo htmlspecialchars($edit_bus['bus_type'] ?? ''); ?>"
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                
                <div>
                    <label for="operator_name" class="block text-sm font-medium text-gray-700">Operator Name / Company</label>
                    <input type="text" name="operator_name" id="operator_name" required
                           value="<?php echo htmlspecialchars($edit_bus['bus_operator'] ?? ''); ?>"
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                
                <div>
                    <label for="rows" class="block text-sm font-medium text-gray-700">Seat Rows</label>
                    <input type="number" name="rows" id="rows" required min="1"
                           value="<?php echo htmlspecialchars($edit_bus['rows'] ?? '10'); ?>"
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                
                <div>
                    <label for="columns" class="block text-sm font-medium text-gray-700">Seat Columns</label>
                    <input type="number" name="columns" id="columns" required min="1"
                           value="<?php echo htmlspecialchars($edit_bus['columns'] ?? '4'); ?>"
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                
                <div class="md:col-span-2">
                    <label for="amenities" class="block text-sm font-medium text-gray-700">Amenities (Comma Separated)</label>
                    <input type="text" name="amenities" id="amenities"
                           value="<?php echo htmlspecialchars($edit_bus['amenities'] ?? 'AC, WiFi, Charging Ports'); ?>"
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g., AC, WiFi, Charging Ports">
                </div>

                <div class="md:col-span-4 flex justify-end space-x-2 mt-2">
                    <button type="submit" class="w-auto py-2 px-6 border border-transparent rounded-md shadow-sm text-sm font-medium text-white 
                        <?php echo $edit_bus ? 'bg-orange-500 hover:bg-orange-600' : 'bg-indigo-600 hover:bg-indigo-700'; ?> focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <?php echo $edit_bus ? 'Update Bus' : 'Create Bus'; ?>
                    </button>
                    <?php if ($edit_bus): ?>
                       <a href="bus_management.php" class="py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-gray-50 hover:bg-gray-100">Cancel</a>
                    <?php endif; ?>
                </div>
                
            </form>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-lg">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2">Bus Fleet</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bus Plate</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type / Model</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Seats</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Operator</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($buses)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No buses found in the fleet. Add one above!</td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach ($buses as $bus): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($bus['bus_id']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($bus['bus_plate']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($bus['bus_type']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($bus['total_seats']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($bus['bus_operator']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                    <a href="bus_management.php?action=edit&id=<?php echo htmlspecialchars($bus['bus_id']); ?>" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                    
                                    <form method="POST" action="bus_management.php" class="inline" onsubmit="return confirm('Are you sure you want to delete bus <?php echo htmlspecialchars($bus['bus_plate']); ?>? This action cannot be undone.');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="bus_id" value="<?php echo htmlspecialchars($bus['bus_id']); ?>">
                                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
    
    <script>
        // Set default capacity/rows/columns if the form is in 'create' mode
        if (!document.getElementById('bus_id')) {
            document.getElementById('capacity').value = 40; // Default total seats
            document.getElementById('rows').value = 10;
            document.getElementById('columns').value = 4;
        }
    </script>
</body>
</html>