<?php
// route_management.php

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
require_once 'controllers/RouteController.php'; 

$routeController = new RouteController();
$message = '';
$message_type = ''; 

// Initialize route data for the form (used for update/edit mode)
$edit_route = null;
$action = $_GET['action'] ?? '';
$route_id = $_GET['id'] ?? null;


// 3. HANDLE POST REQUESTS (CREATE, UPDATE, DELETE)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $post_action = $_POST['action'] ?? '';
    
    if ($post_action == 'create') {
        $result = $routeController->createRoute($_POST);
    } elseif ($post_action == 'update') {
        $result = $routeController->updateRoute($_POST);
    } elseif ($post_action == 'delete') {
        $result = $routeController->deleteRoute($_POST['route_id'] ?? null);
    } else {
        $result = ['success' => false, 'message' => 'Invalid action specified.'];
    }

    $message = $result['message'];
    $message_type = $result['success'] ? 'success' : 'error';
    
    // Redirect on success to clear POST data and show message
    if ($result['success']) {
        header("Location: route_management.php?msg=" . urlencode($message) . "&type=" . $message_type);
        exit;
    }
}

// 4. HANDLE GET REQUESTS (MESSAGES, EDIT MODE)
if (isset($_GET['msg']) && isset($_GET['type'])) {
    $message = htmlspecialchars($_GET['msg']);
    $message_type = htmlspecialchars($_GET['type']);
}

// Check for Edit Mode
if ($action == 'edit' && $route_id) {
    $edit_route = $routeController->getRouteById($route_id); 
    if (!$edit_route) {
        $message = "Route record not found.";
        $message_type = 'error';
        $action = ''; 
    }
}

// 5. FETCH ALL ROUTES FOR THE LIST VIEW
$routes = $routeController->index(); 

// 6. Define Nav Items for Sidebar (copied from admin_dashboard)
$admin_name = htmlspecialchars($_SESSION['name'] ?? 'Administrator');
const CURRENCY_SYMBOL = 'RWF '; // Assuming you use this constant globally
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
    <title>Route Management - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom Tailwind configuration based on your dashboard */
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
                            <?php echo ($file == 'route_management.php') ? 'bg-indigo-600 font-semibold' : ''; ?>">
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
        <h1 class="text-4xl font-extrabold text-gray-900 mb-6">Route Management</h1>
        
        <?php if ($message): ?>
            <div class="p-4 mb-4 rounded-lg <?php echo ($message_type == 'success') ? 'bg-green-100 text-green-700 border border-green-400' : 'bg-red-100 text-red-700 border border-red-400'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="bg-white p-6 rounded-xl shadow-lg mb-8">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2">
                <?php echo $edit_route ? 'Edit Route: ' . htmlspecialchars($edit_route['route_name']) : 'Define New Route'; ?>
            </h2>
            
            <form method="POST" action="route_management.php" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <input type="hidden" name="action" value="<?php echo $edit_route ? 'update' : 'create'; ?>">
                <?php if ($edit_route): ?>
                    <input type="hidden" name="route_id" value="<?php echo htmlspecialchars($edit_route['route_id']); ?>">
                <?php endif; ?>

                <div class="md:col-span-2">
                    <label for="route_name" class="block text-sm font-medium text-gray-700">Route Name (e.g., Kigali-Musanze)</label>
                    <input type="text" name="route_name" id="route_name" required
                           value="<?php echo htmlspecialchars($edit_route['route_name'] ?? ''); ?>"
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                
                <div>
                    <label for="departure_location" class="block text-sm font-medium text-gray-700">Departure Location</label>
                    <input type="text" name="departure_location" id="departure_location" required
                           value="<?php echo htmlspecialchars($edit_route['departure_location'] ?? ''); ?>"
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label for="destination_location" class="block text-sm font-medium text-gray-700">Destination Location</label>
                    <input type="text" name="destination_location" id="destination_location" required
                           value="<?php echo htmlspecialchars($edit_route['destination_location'] ?? ''); ?>"
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label for="fare_base" class="block text-sm font-medium text-gray-700">Base Fare (<?php echo CURRENCY_SYMBOL; ?>)</label>
                    <input type="number" step="0.01" name="fare_base" id="fare_base" required min="1"
                           value="<?php echo htmlspecialchars($edit_route['fare_base'] ?? ''); ?>"
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div class="md:col-span-5 flex justify-end items-end space-x-2">
                    <?php if ($edit_route): ?>
                         <a href="route_management.php" class="py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-gray-50 hover:bg-gray-100">Cancel Edit</a>
                    <?php endif; ?>
                    <button type="submit" class="w-auto py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white 
                        <?php echo $edit_route ? 'bg-orange-500 hover:bg-orange-600' : 'bg-indigo-600 hover:bg-indigo-700'; ?> focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <?php echo $edit_route ? 'Update Route' : 'Create Route'; ?>
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-lg">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2">Available Routes</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Route Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Departure</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Destination</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Base Fare</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($routes)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No routes defined yet.</td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach ($routes as $route): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($route['route_id']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($route['route_name']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($route['departure_location']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($route['destination_location']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo CURRENCY_SYMBOL . number_format($route['fare_base'], 0); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                    <a href="route_management.php?action=edit&id=<?php echo htmlspecialchars($route['route_id']); ?>" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                    
                                    <form method="POST" action="route_management.php" class="inline" onsubmit="return confirm('WARNING: Deleting this route will affect all linked schedules. Are you sure you want to delete <?php echo htmlspecialchars($route['route_name']); ?>?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="route_id" value="<?php echo htmlspecialchars($route['route_id']); ?>">
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
</body>
</html>