<?php
// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 1. Authorization Check
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['user_type'] !== 'user') {
    header("Location: login.php");
    exit;
}

// 2. Include Controller/Model (We'll use a new method for fetching booking details)
require_once 'models/Bus.php';
$busModel = new Bus();

// Currency Constant
const CURRENCY_SYMBOL = 'RWF ';

$booking_id = $_GET['booking_id'] ?? null;
$booking_details = null;
$error_message = '';

// 3. Fetch Booking Details
if (is_numeric($booking_id)) {
    // You will need to add a getBookingDetails method to your Bus Model!
    $booking_details = $busModel->getBookingDetails((int)$booking_id);

    if (!$booking_details || $booking_details['user_id'] != $_SESSION['user_id']) {
        // Prevent users from viewing other people's tickets
        $error_message = "Booking not found or you do not have permission to view this ticket.";
    }
} else {
    $error_message = "Invalid booking reference.";
}

// Function to generate a simple reference/QR data
function generate_ticket_reference($booking_id) {
    // A simple, unique reference number
    return 'BBK-' . date('Ymd') . '-' . str_pad($booking_id, 5, '0', STR_PAD_LEFT);
}

// Generate the reference if details are available
$ticket_reference = $booking_details ? generate_ticket_reference($booking_details['booking_id']) : '';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed - E-Ticket</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrious@4.0.2/dist/qrious.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary-indigo': '#4f46e5',
                        'success-green': '#10b981', // Emerald green
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100 min-h-screen">
    
    <main class="max-w-4xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        
        <?php if ($error_message): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-8" role="alert">
                <h1 class="text-xl font-bold mb-2">Error!</h1>
                <?php echo htmlspecialchars($error_message); ?>
                <p class="mt-2"><a href="user_dashboard.php" class="font-bold underline">Go back to Search</a></p>
            </div>
        <?php elseif ($booking_details): ?>
            
            <div class="bg-white shadow-2xl rounded-xl overflow-hidden border-t-8 border-success-green">
                
                <div class="p-8 bg-success-green text-white">
                    <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <h1 class="text-4xl font-extrabold mb-1">BOOKING CONFIRMED!</h1>
                    <p class="text-lg">Your E-Ticket is ready.</p>
                </div>
                
                <div class="p-8">
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 border-b pb-4 border-dashed border-gray-300">
                        <div class="col-span-1">
                            <p class="text-sm text-gray-500">BOOKING REFERENCE</p>
                            <p class="text-2xl font-bold text-primary-indigo"><?php echo htmlspecialchars($ticket_reference); ?></p>
                        </div>
                        <div class="col-span-1">
                            <p class="text-sm text-gray-500">PASSENGER</p>
                            <p class="text-xl font-semibold text-gray-800"><?php echo htmlspecialchars($_SESSION['name']); ?></p>
                        </div>
                        <div class="col-span-1">
                            <p class="text-sm text-gray-500">AMOUNT PAID</p>
                            <p class="text-2xl font-bold text-success-green"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($booking_details['total_amount'], 0); ?></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-10 mb-8">
                        
                        <div>
                            <h3 class="text-xl font-bold text-gray-800 mb-3">TRIP DETAILS</h3>
                            <p class="text-lg">**Route:** <?php echo htmlspecialchars($booking_details['departure_location']); ?> &rarr; <?php echo htmlspecialchars($booking_details['destination_location']); ?></p>
                            <p class="text-lg">**Departure:** <?php echo date('l, M j, Y', strtotime($booking_details['departure_time'])); ?></p>
                            <p class="text-2xl font-extrabold text-primary-indigo mt-1"><?php echo date('H:i', strtotime($booking_details['departure_time'])); ?></p>
                            <p class="text-base text-gray-600 mt-2">**Bus Operator:** <?php echo htmlspecialchars($booking_details['bus_operator']); ?></p>
                            <p class="text-base text-gray-600">**Bus Type:** <?php echo htmlspecialchars($booking_details['bus_type']); ?></p>
                        </div>

                        <div class="flex flex-col items-center md:items-start">
                            <h3 class="text-xl font-bold text-gray-800 mb-3">YOUR SEATS</h3>
                            <div class="flex flex-wrap gap-2 mb-4">
                                <?php foreach ($booking_details['seats'] as $seat): ?>
                                    <span class="bg-primary-indigo text-white text-lg font-bold px-4 py-2 rounded-lg shadow-md"><?php echo htmlspecialchars($seat['seat_number']); ?></span>
                                <?php endforeach; ?>
                            </div>
                            
                            <h3 class="text-xl font-bold text-gray-800 mt-4 mb-3">TICKET QR CODE</h3>
                            <canvas id="qrcode-canvas" class="p-2 border border-gray-300 rounded-lg bg-white"></canvas>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 p-6 flex justify-end gap-4">
                    <button onclick="window.print()" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 font-semibold">
                        Print Ticket
                    </button>
                    <a href="user_dashboard.php" class="px-6 py-2 bg-primary-indigo text-white rounded-lg hover:bg-indigo-700 font-semibold">
                        Book Another Trip
                    </a>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const ticketReference = "<?php echo $ticket_reference; ?>";
                    const canvas = document.getElementById('qrcode-canvas');
                    
                    if (canvas && ticketReference) {
                        new QRious({
                            element: canvas,
                            value: 'BUSBOOK_TICKET:' + ticketReference, // Unique string for verification
                            size: 150
                        });
                    }
                });
            </script>
        
        <?php endif; ?>
    </main>
</body>
</html>