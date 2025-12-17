<?php
// schedule_management.php

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
require_once 'controllers/ScheduleController.php'; 

$scheduleController = new ScheduleController();
$message = '';
$message_type = ''; 
// NOTE: Ensure CURRENCY_SYMBOL is defined
if (!defined('CURRENCY_SYMBOL')) {
    define('CURRENCY_SYMBOL', 'RWF ');
}

// Fetch dependencies for the form
$routes = $scheduleController->getRoutes();
$buses = $scheduleController->getBuses();

// Initialize schedule data for the form (used for update/edit mode)
$edit_schedule = null;
$action = $_GET['action'] ?? '';
$schedule_id = $_GET['id'] ?? null;


// 3. HANDLE POST REQUESTS (CREATE, UPDATE, DELETE)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $post_action = $_POST['action'] ?? '';
    
    if ($post_action == 'create') {
        $result = $scheduleController->createSchedule($_POST);
    } elseif ($post_action == 'update') {
        $result = $scheduleController->updateSchedule($_POST);
    } elseif ($post_action == 'delete') {
        $result = $scheduleController->deleteSchedule($_POST['schedule_id'] ?? null);
    } else {
        $result = ['success' => false, 'message' => 'Invalid action specified.'];
    }

    $message = $result['message'];
    $message_type = $result['success'] ? 'success' : 'error';
   
    if ($result['success']) {
        header("Location: schedule_management.php?msg=" . urlencode($message) . "&type=" . $message_type);
        exit;
    }
}

if (isset($_GET['msg']) && isset($_GET['type'])) {
    $message = htmlspecialchars($_GET['msg']);
    $message_type = htmlspecialchars($_GET['type']);
}

if ($action == 'edit' && $schedule_id) {
    $edit_schedule = $scheduleController->getScheduleById($schedule_id); 
    if (!$edit_schedule) {
        $message = "Schedule record not found.";
        $message_type = 'error';
        $action = ''; 
    }
}

$schedules = $scheduleController->index(); 

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
$statuses = ['Scheduled', 'Departed', 'Cancelled'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Management - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        input[type="datetime-local"] {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
        }
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
                            <?php echo ($file == 'schedule_management.php') ? 'bg-indigo-600 font-semibold' : ''; ?>">
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
        <h1 class="text-4xl font-extrabold text-gray-900 mb-6">Schedule Management</h1>
        
        <?php if ($message): ?>
            <div class="p-4 mb-4 rounded-lg <?php echo ($message_type == 'success') ? 'bg-green-100 text-green-700 border border-green-400' : 'bg-red-100 text-red-700 border border-red-400'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="bg-white p-6 rounded-xl shadow-lg mb-8">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2">
                <?php echo $edit_schedule ? 'Edit Schedule ID: ' . htmlspecialchars($edit_schedule['schedule_id']) : 'Create New Schedule'; ?>
            </h2>
            
            <?php if (empty($routes) || empty($buses)): ?>
                <div class="p-4 bg-yellow-100 text-yellow-700 border border-yellow-400 rounded-md">
                    ⚠️ Cannot create schedules. Please first define at least one <a href="bus_management.php" class="font-semibold underline">Bus</a> and one <a href="route_management.php" class="font-semibold underline">Route</a>.
                </div>
            <?php else: ?>

                <form method="POST" action="schedule_management.php" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <input type="hidden" name="action" value="<?php echo $edit_schedule ? 'update' : 'create'; ?>">
                    <?php if ($edit_schedule): ?>
                        <input type="hidden" name="schedule_id" value="<?php echo htmlspecialchars($edit_schedule['schedule_id']); ?>">
                    <?php endif; ?>

                    <div>
                        <label for="bus_id" class="block text-sm font-medium text-gray-700">Select Bus</label>
                        <select name="bus_id" id="bus_id" required
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                            <option value="">-- Choose Bus --</option>
                            <?php foreach ($buses as $bus): ?>
                                <option value="<?php echo htmlspecialchars($bus['bus_id']); ?>" 
                                    <?php echo ($edit_schedule && $edit_schedule['bus_id'] == $bus['bus_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($bus['registration_number'] . ' (' . $bus['capacity'] . ' seats)'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="route_id" class="block text-sm font-medium text-gray-700">Select Route</label>
                        <select name="route_id" id="route_id" required
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                            <option value="">-- Choose Route --</option>
                            <?php foreach ($routes as $route): ?>
                                <option value="<?php echo htmlspecialchars($route['route_id']); ?>" 
                                    <?php echo ($edit_schedule && $edit_schedule['route_id'] == $route['route_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($route['route_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="departure_time" class="block text-sm font-medium text-gray-700">Departure Time</label>
                        <?php
                            $dep_time_value = '';
                            if ($edit_schedule && isset($edit_schedule['departure_time'])) {
                                try {
                                    $dt = new DateTime($edit_schedule['departure_time']);
                                    $dep_time_value = $dt->format('Y-m-d\TH:i');
                                } catch (Exception $e) {
                                }
                            }
                        ?>
                        <input type="datetime-local" name="departure_time" id="departure_time" required
                            value="<?php echo htmlspecialchars($dep_time_value); ?>"
                            min="<?php echo date('Y-m-d\TH:i'); ?>"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" id="status" required
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                            <?php foreach ($statuses as $status): ?>
                                <option value="<?php echo htmlspecialchars($status); ?>" 
                                    <?php echo ($edit_schedule && $edit_schedule['status'] == $status) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($status); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="flex items-end space-x-2">
                        <?php if ($edit_schedule): ?>
                            <a href="schedule_management.php" class="py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-gray-50 hover:bg-gray-100">Cancel Edit</a>
                        <?php endif; ?>
                        <button type="submit" class="w-full py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white 
                            <?php echo $edit_schedule ? 'bg-orange-500 hover:bg-orange-600' : 'bg-indigo-600 hover:bg-indigo-700'; ?> focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <?php echo $edit_schedule ? 'Update Schedule' : 'Create Schedule'; ?>
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-lg">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2">Current Schedules</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Route</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bus</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fare</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($schedules)): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">No schedules created yet.</td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach ($schedules as $schedule): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($schedule['schedule_id']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" title="<?php echo htmlspecialchars($schedule['departure_location']) . ' to ' . htmlspecialchars($schedule['destination_location']); ?>">
                                    <?php echo htmlspecialchars($schedule['route_name']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" title="Capacity: <?php echo htmlspecialchars($schedule['capacity']); ?> seats">
                                    <?php echo htmlspecialchars($schedule['bus_reg']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('M d, Y h:i A', strtotime($schedule['departure_time'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?php echo CURRENCY_SYMBOL . number_format($schedule['fare_base'], 0); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold">
                                    <span class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-medium 
                                        <?php 
                                            if ($schedule['status'] == 'Scheduled') echo 'bg-green-100 text-green-800';
                                            elseif ($schedule['status'] == 'Departed') echo 'bg-yellow-100 text-yellow-800';
                                            else echo 'bg-red-100 text-red-800';
                                        ?>">
                                        <?php echo htmlspecialchars($schedule['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                    <a href="schedule_management.php?action=edit&id=<?php echo htmlspecialchars($schedule['schedule_id']); ?>" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                    
                                    <form method="POST" action="schedule_management.php" class="inline" onsubmit="return confirm('WARNING: Deleting this schedule will invalidate all linked bookings. Are you sure you want to delete Schedule ID <?php echo htmlspecialchars($schedule['schedule_id']); ?>?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="schedule_id" value="<?php echo htmlspecialchars($schedule['schedule_id']); ?>">
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