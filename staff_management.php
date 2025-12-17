<?php
// staff_management.php

// 1. SESSION AND SECURITY CHECK
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Security Check: Ensure user is logged in AND is of type 'admin'
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit;
}

require_once 'controllers/StaffController.php'; 

$staffController = new StaffController();
$message = '';
$message_type = ''; 

$edit_staff = null;
$action = $_GET['action'] ?? '';
$user_id = $_GET['id'] ?? null;
$staff_roles = ['driver', 'staff'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $post_action = $_POST['action'] ?? '';
    
    if ($post_action == 'create') {
        $result = $staffController->createStaff($_POST);
    } elseif ($post_action == 'update') {
        $result = $staffController->updateStaff($_POST);
    } elseif ($post_action == 'delete') {
        $result = $staffController->deleteStaff($_POST['user_id'] ?? null);
    } else {
        $result = ['success' => false, 'message' => 'Invalid action specified.'];
    }

    $message = $result['message'];
    $message_type = $result['success'] ? 'success' : 'error';
    
    if ($result['success']) {
        header("Location: staff_management.php?msg=" . urlencode($message) . "&type=" . $message_type);
        exit;
    }
}

if (isset($_GET['msg']) && isset($_GET['type'])) {
    $message = htmlspecialchars($_GET['msg']);
    $message_type = htmlspecialchars($_GET['type']);
}

if ($action == 'edit' && $user_id) {
    $edit_staff = $staffController->getStaffById($user_id); 
    if (!$edit_staff) {
        $message = "Staff record not found.";
        $message_type = 'error';
        $action = ''; 
    }
}
$staff_members = $staffController->index(); 

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
    <title>Staff Management - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
                            <?php echo ($file == 'staff_management.php') ? 'bg-indigo-600 font-semibold' : ''; ?>">
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
        <h1 class="text-4xl font-extrabold text-gray-900 mb-6">Driver & Staff Management</h1>
        
        <?php if ($message): ?>
            <div class="p-4 mb-4 rounded-lg <?php echo ($message_type == 'success') ? 'bg-green-100 text-green-700 border border-green-400' : 'bg-red-100 text-red-700 border border-red-400'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="bg-white p-6 rounded-xl shadow-lg mb-8">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2">
                <?php echo $edit_staff ? 'Edit Staff ID: ' . htmlspecialchars($edit_staff['user_id']) : 'Register New Staff Member'; ?>
            </h2>
            
            <form method="POST" action="staff_management.php" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <input type="hidden" name="action" value="<?php echo $edit_staff ? 'update' : 'create'; ?>">
                <?php if ($edit_staff): ?>
                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($edit_staff['user_id']); ?>">
                <?php endif; ?>

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                    <input type="text" name="name" id="name" required
                        value="<?php echo htmlspecialchars($edit_staff['name'] ?? ''); ?>"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" id="email" required
                        value="<?php echo htmlspecialchars($edit_staff['email'] ?? ''); ?>"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                    <input type="text" name="phone" id="phone" required
                        value="<?php echo htmlspecialchars($edit_staff['phone'] ?? ''); ?>"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                
                <div>
                    <label for="user_type" class="block text-sm font-medium text-gray-700">Role</label>
                    <select name="user_type" id="user_type" required
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                        <option value="">-- Select Role --</option>
                        <?php foreach ($staff_roles as $role): ?>
                            <option value="<?php echo htmlspecialchars($role); ?>" 
                                <?php echo ($edit_staff && $edit_staff['user_type'] == $role) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars(ucfirst($role)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="employee_id" class="block text-sm font-medium text-gray-700">Employee ID</label>
                    <input type="text" name="employee_id" id="employee_id" required
                        value="<?php echo htmlspecialchars($edit_staff['employee_id'] ?? ''); ?>"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label for="license_number" class="block text-sm font-medium text-gray-700">License Number (If Driver)</label>
                    <input type="text" name="license_number" id="license_number" 
                        value="<?php echo htmlspecialchars($edit_staff['license_number'] ?? ''); ?>"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div class="<?php echo $edit_staff ? 'hidden' : ''; ?>">
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" name="password" id="password" 
                        <?php echo $edit_staff ? '' : 'required'; ?>
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <?php if ($edit_staff): ?>
                        <p class="text-xs text-gray-500 mt-1">Leave blank to keep current password.</p>
                    <?php endif; ?>
                </div>

                <div class="flex items-end space-x-2 mt-auto col-span-full md:col-span-1">
                    <?php if ($edit_staff): ?>
                        <a href="staff_management.php" class="py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-gray-50 hover:bg-gray-100">Cancel Edit</a>
                    <?php endif; ?>
                    <button type="submit" class="w-full py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white 
                        <?php echo $edit_staff ? 'bg-orange-500 hover:bg-orange-600' : 'bg-indigo-600 hover:bg-indigo-700'; ?> focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <?php echo $edit_staff ? 'Update Staff' : 'Register Staff'; ?>
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-lg">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2">Current Staff and Drivers</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID / Employee ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name / Contact</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">License Number</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Created</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($staff_members)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No staff members or drivers registered yet.</td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach ($staff_members as $staff): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="text-gray-900 font-semibold">User: #<?php echo htmlspecialchars($staff['user_id']); ?></div>
                                    <div class="text-indigo-600 text-xs font-medium">Emp ID: <?php echo htmlspecialchars($staff['employee_id']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="text-gray-900 font-medium"><?php echo htmlspecialchars($staff['name']); ?></div>
                                    <div class="text-gray-500 text-xs" title="<?php echo htmlspecialchars($staff['email']); ?>"><?php echo htmlspecialchars($staff['phone']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold">
                                    <span class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-medium 
                                        <?php echo ($staff['user_type'] == 'driver') ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'; ?>">
                                        <?php echo htmlspecialchars(ucfirst($staff['user_type'])); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo htmlspecialchars($staff['license_number'] ?? 'N/A'); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('M d, Y', strtotime($staff['created_at'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                    <a href="staff_management.php?action=edit&id=<?php echo htmlspecialchars($staff['user_id']); ?>" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                    
                                    <form method="POST" action="staff_management.php" class="inline" onsubmit="return confirm('WARNING: Deleting staff will remove their user record. Are you sure you want to delete <?php echo htmlspecialchars($staff['name']); ?> (ID <?php echo htmlspecialchars($staff['user_id']); ?>)?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($staff['user_id']); ?>">
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