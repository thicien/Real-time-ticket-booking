<?php
// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Security Constant: This token secures the public QR link view.
// !!! IMPORTANT: CHANGE THIS TO A LONG, COMPLEX STRING !!!
const TICKET_VIEW_SECRET = 'your_long_and_secret_token_to_verify_qr_view'; 

// --- FIX FOR LOCAL TESTING ---
// 1. HARDCODED IP ADDRESS for QR Code generation (as requested).
// This is your computer's local IP address, which your phone uses to access XAMPP.
const LOCAL_HOST_IP = '192.168.137.1'; 


// 2. Include Controller/Model 
require_once 'models/Bus.php';
$busModel = new Bus();

// Currency Constant
const CURRENCY_SYMBOL = 'RWF ';

$booking_id = $_GET['booking_id'] ?? null;
$booking_details = null;
$error_message = '';

// --- NEW AUTHORIZATION LOGIC ---

// Check for QR token in the URL
$qr_token = $_GET['token'] ?? '';

// Determines if the user is attempting to view the ticket via the public QR link
$is_qr_view = (is_numeric($booking_id) && $qr_token === TICKET_VIEW_SECRET);

// 1. Authorization Check
// If the user is NOT logged in AND they are NOT using the secure QR link, redirect to login.
if ((!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['user_type'] !== 'user') && !$is_qr_view) {
    header("Location: login.php");
    exit;
}

// --- END NEW AUTHORIZATION LOGIC ---


// Function to generate a simple reference/QR data
function generate_ticket_reference($booking_id) {
    // A simple, unique reference number for display purposes
    return 'BBK-' . date('Ymd') . '-' . str_pad($booking_id, 5, '0', STR_PAD_LEFT);
}


// 3. Fetch Booking Details
if (is_numeric($booking_id)) {
    // getBookingDetails MUST exist in Bus Model and return booking data + trip data + seats array
    $booking_details = $busModel->getBookingDetails((int)$booking_id); 

    // Additional security check: If logged in, ensure the ticket belongs to the user.
    if (!$booking_details || (isset($_SESSION['user_id']) && $booking_details['user_id'] != $_SESSION['user_id'] && !$is_qr_view)) {
        $error_message = "Booking not found or access denied.";
    }
} else {
    $error_message = "Invalid booking reference.";
}

// 4. Prepare Data for Display and QR Code
$ticket_reference = '';
$qr_code_data_string = 'ERROR: No Booking Data'; 

if ($booking_details) {
    // 4a. Human-readable reference for display
    $ticket_reference = generate_ticket_reference($booking_details['booking_id']);
    
    // --- START QR CODE URL CONSTRUCTION (THE FIX) ---
    
    // 4b. Machine-readable, unique URL for QR code
    
    // Use the hardcoded IP for mobile access
    $host = LOCAL_HOST_IP; 
    $protocol = "http://"; // Assuming XAMPP is running on HTTP
    
    // Get the base path (e.g., /Online-ticket-booking)
    $path = dirname($_SERVER['PHP_SELF']);
    // Clean up path for the file (e.g., /Online-ticket-booking/confirmation.php)
    $path = rtrim($path, '/') . '/confirmation.php'; 

    // The link includes the booking_id and the TICKET_VIEW_SECRET for public access
    $qr_code_data_string = $protocol . $host . $path . '?booking_id=' . $booking_details['booking_id'] . '&token=' . TICKET_VIEW_SECRET;
    
    // --- END QR CODE URL CONSTRUCTION ---
}

// Use json_encode for safe transfer of the QR string to JavaScript
$js_qr_code_data = json_encode($qr_code_data_string);

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
                            <p class="text-xl font-semibold text-gray-800"><?php echo htmlspecialchars($_SESSION['name'] ?? 'Guest'); ?></p>
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
                            <h3 class="text-xl font-bold text-gray-800 mb-3">TICKET QR CODE</h3>
                            <p class="text-sm text-gray-500 mb-2">Scan to open ticket link.</p>
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
                    const qrCodeData = <?php echo $js_qr_code_data; ?>;
                    const canvas = document.getElementById('qrcode-canvas');
                    
                    if (canvas && qrCodeData && qrCodeData.startsWith('http')) {
                        new QRious({
                            element: canvas,
                            value: qrCodeData, 
                            size: 150 
                        });
                        console.log('QR Code generated successfully with URL:', qrCodeData);
                    } else {
                        console.error('QR Code generation failed. Missing or invalid URL data:', qrCodeData);
                        const ctx = canvas.getContext('2d');
                        ctx.font = '10px Arial';
                        ctx.fillText('QR Link Failed', 10, 75);
                    }
                });
            </script>
        
        <?php endif; ?>
    </main>
</body>
</html>