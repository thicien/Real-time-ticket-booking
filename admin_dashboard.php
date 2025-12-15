<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit;
}

require_once 'models/Admin.php';
$adminModel = new Admin();

$admin_name = htmlspecialchars($_SESSION['name'] ?? 'Administrator');
const CURRENCY_SYMBOL = 'RWF ';
$raw_stats = $adminModel->getDashboardStats(); 

$stats = [
    'Total Bookings' => number_format($raw_stats['Total Bookings']),
    'Total Revenue' => CURRENCY_SYMBOL . number_format($raw_stats['Total Revenue'], 0),
    'Active Routes' => number_format($raw_stats['Active Routes']),
    'Seat Occupancy' => htmlspecialchars($raw_stats['Seat Occupancy'])
];


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
    <title>Admin Dashboard</title>
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
<body class="bg-gray-100 min-h-screen flex">

    <div class="w-64 bg-gray-800 text-white flex flex-col p-4 shadow-2xl">
        <h2 class="text-2xl font-bold mb-8 border-b border-gray-700 pb-4 text-primary-indigo">Admin Panel</h2>
        <nav class="flex-grow">
            <ul class="space-y-2">
                <li>
                    <a href="admin_dashboard.php" class="flex items-center p-2 rounded-lg bg-primary-indigo font-semibold">
                        Dashboard
                    </a>
                </li>
                <?php foreach ($nav_items as $label => $file): ?>
                    <li>
                        <a href="<?php echo htmlspecialchars($file); ?>" class="flex items-center p-2 rounded-lg hover:bg-gray-700 transition duration-150">
                            <?php echo htmlspecialchars($label); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
        
        <div class="mt-auto pt-4 border-t border-gray-700">
            <p class="text-sm mb-2">Logged in as: <span class="font-semibold"><?php echo $admin_name; ?></span></p>
            <a href="logout.php" class="flex items-center p-2 rounded-lg text-red-400 hover:bg-gray-700 transition duration-150">
                Logout
            </a>
        </div>
    </div>

    <div class="flex-grow p-8">
        <h1 class="text-4xl font-extrabold text-gray-900 mb-10">System Overview</h1>
        

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
            <?php foreach ($stats as $label => $value): ?>
                <div class="bg-white p-6 rounded-xl shadow-lg border-t-4 border-primary-indigo">
                    <p class="text-sm font-medium text-gray-500 uppercase"><?php echo htmlspecialchars($label); ?></p>
                    <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo htmlspecialchars($value); ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="bg-white p-8 rounded-xl shadow-lg">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-3">Quick Actions</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="bus_management.php?action=add" class="text-center p-4 bg-primary-indigo/10 text-primary-indigo rounded-lg hover:bg-primary-indigo hover:text-white transition duration-200 font-medium">
                    + Add New Bus
                </a>
                <a href="schedule_management.php?action=add" class="text-center p-4 bg-success-green/10 text-success-green rounded-lg hover:bg-success-green hover:text-white transition duration-200 font-medium">
                    + Add Schedule
                </a>
                <a href="booking_management.php?action=create" class="text-center p-4 bg-yellow-500/10 text-yellow-500 rounded-lg hover:bg-yellow-500 hover:text-white transition duration-200 font-medium">
                    Manual Booking
                </a>
                <a href="payments_management.php" class="text-center p-4 bg-gray-500/10 text-gray-700 rounded-lg hover:bg-gray-700 hover:text-white transition duration-200 font-medium">
                    View Payments
                </a>
            </div>
        </div>
    </div>

</body>
</html>