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
require_once 'controllers/BusController.php';
require_once 'models/Bus.php'; 
require_once 'services/SmsService.php';

$user_id = $_SESSION['user_id'];
$user_name = htmlspecialchars($_SESSION['name']);
$error_message = '';
$booking_data = [];

const CURRENCY_SYMBOL = 'RWF ';

if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['finalize_payment'])) {
    $schedule_id = $_POST['schedule_id'] ?? null;
    $price_per_seat = $_POST['price'] ?? null;
    $selected_seats_str = $_POST['selected_seats'] ?? '';
    $selected_seats = array_filter(explode(',', $selected_seats_str));
    $total_seats = count($selected_seats);
    $total_amount = $total_seats * (float)$price_per_seat;

    if (!$schedule_id || $total_seats === 0 || $total_amount === 0) {
        $error_message = "Invalid booking details. Please go back and select seats.";
    } else {
        $busController = new BusController();
        $trip = $busController->getScheduleDetailsWithSeats((int)$schedule_id); 

        if (!$trip) {
            $error_message = "Trip not found.";
        } else {
            $booking_data = [
                'schedule_id' => (int)$schedule_id,
                'trip' => $trip,
                'selected_seats' => $selected_seats,
                'price_per_seat' => (float)$price_per_seat,
                'total_amount' => $total_amount,
            ];
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['finalize_payment'])) {
    $schedule_id = (int)($_POST['schedule_id'] ?? 0);
    $total_amount = (float)($_POST['total_amount'] ?? 0);
    $selected_seats = array_filter(explode(',', $_POST['selected_seats'] ?? ''));
    $payment_method = $_POST['payment_method'] ?? '';

    if ($schedule_id === 0 || $total_amount === 0 || empty($selected_seats)) {
        $error_message = "Missing critical booking data. Cannot finalize payment.";
    }

    $temp_reference = 'BBK-' . date('YmdHi') . '-' . substr(md5(mt_rand()), 0, 4);

    $payment_success = true;

    if ($payment_success && empty($error_message)) {
        $busModel = new Bus();
        $booking_id = $busModel->createBooking($user_id, $schedule_id, $total_amount, $selected_seats);

        if ($booking_id) {
            
            $user_details = $busModel->getUserDetails($user_id);
            $trip_details = $busModel->getScheduleDetails($schedule_id); 
            
            if ($user_details && $trip_details) {
                $phone_number = $user_details['phone_number'] ?? null;
                
                if ($phone_number) {
                    $seats_str = implode(',', $selected_seats);

                    $sms_message = sprintf(
                        "BusBook Confirmed: Ref %s. %s to %s, %s at %s. Seats: %s. Total: %s%s. Enjoy your trip!",
                        $temp_reference,
                        $trip_details['departure_location'],
                        $trip_details['destination_location'],
                        date('M j', strtotime($trip_details['departure_time'])),
                        date('H:i', strtotime($trip_details['departure_time'])),
                        $seats_str,
                        CURRENCY_SYMBOL,
                        number_format($total_amount, 0)
                    );
                    
                    SmsService::sendSms($phone_number, $sms_message);
                }
            }
        
            header("Location: confirmation.php?booking_id=" . $booking_id);
            exit;
        } else {
            $error_message = "Payment successful, but failed to record booking. Please contact support.";
        }
    } else if (!$payment_success) {
        $error_message = "Payment failed. Please try a different method or check your details.";
    }
    
    if (!empty($error_message)) {
        $busController = new BusController();
        $trip = $busController->getScheduleDetailsWithSeats($schedule_id);
        $booking_data = [
            'schedule_id' => $schedule_id,
            'trip' => $trip,
            'selected_seats' => $selected_seats,
            'price_per_seat' => $trip['price'],
            'total_amount' => $total_amount,
        ];
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment & Confirmation - BusBook</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary-indigo': '#4f46e5',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100 min-h-screen">
    
    <main class="max-w-4xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        
        <h1 class="text-3xl font-extrabold text-gray-900 mb-8 border-b pb-3">Secure Payment</h1>
        
        <?php if ($error_message): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-8" role="alert">
                <?php echo htmlspecialchars($error_message); ?>
                <p class="mt-2"><a href="user_dashboard.php" class="font-bold underline">Go back to Search</a></p>
            </div>
        <?php elseif (!empty($booking_data)): 
            $trip = $booking_data['trip'];
        ?>
            
            <div class="flex flex-col lg:flex-row gap-8">
                
                <div class="lg:w-1/2 bg-white shadow-xl rounded-xl p-6 md:p-8 h-fit">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">Trip & Seat Details</h2>
                    
                    <div class="space-y-3 text-sm">
                        <p><strong>Route:</strong> <span class="text-primary-indigo font-medium"><?php echo htmlspecialchars($trip['departure_location']); ?> &rarr; <?php echo htmlspecialchars($trip['destination_location']); ?></span></p>
                        <p><strong>Operator:</strong> <?php echo htmlspecialchars($trip['bus_operator']); ?></p>
                        <p><strong>Time:</strong> <?php echo date('l, M j, Y', strtotime($trip['departure_time'])); ?> at <?php echo date('H:i', strtotime($trip['departure_time'])); ?></p>
                        <p><strong>Seats Selected:</strong> <span class="font-bold text-lg text-green-600"><?php echo count($booking_data['selected_seats']); ?></span></p>
                        <p class="mt-4">
                            **Seat Numbers:**
                            <span class="block mt-1 space-x-2">
                            <?php foreach ($booking_data['selected_seats'] as $seat): ?>
                                <span class="inline-block bg-gray-200 text-gray-800 text-xs font-semibold px-2 py-1 rounded-full"><?php echo htmlspecialchars($seat); ?></span>
                            <?php endforeach; ?>
                            </span>
                        </p>
                    </div>

                    <div class="mt-6 pt-4 border-t border-gray-200">
                        <p class="text-xl font-medium text-gray-700">Price per Seat: <?php echo CURRENCY_SYMBOL; ?><?php echo number_format($booking_data['price_per_seat'], 0); ?></p>
                        <p class="text-3xl font-extrabold text-primary-indigo mt-2">TOTAL DUE: <?php echo CURRENCY_SYMBOL; ?><span id="total-price-display"><?php echo number_format($booking_data['total_amount'], 0); ?></span></p>
                    </div>
                </div>

                <div class="lg:w-1/2 bg-white shadow-xl rounded-xl p-6 md:p-8">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">Choose Payment Method</h2>
                    
                    <form action="payment.php" method="POST">
                        <input type="hidden" name="finalize_payment" value="1">
                        <input type="hidden" name="schedule_id" value="<?php echo $booking_data['schedule_id']; ?>">
                        <input type="hidden" name="total_amount" value="<?php echo $booking_data['total_amount']; ?>">
                        <input type="hidden" name="selected_seats" value="<?php echo htmlspecialchars(implode(',', $booking_data['selected_seats'])); ?>">

                        <div class="space-y-4 mb-6">
                            <label class="block p-4 border-2 rounded-lg cursor-pointer hover:border-primary-indigo transition duration-150 has-checked:border-primary-indigo has-checked:bg-indigo-50/50">
                                <input type="radio" name="payment_method" value="Mobile Money (MTN)" required class="mr-2 accent-primary-indigo">
                                <span class="font-medium text-gray-800">Mobile Money (MTN, Airtel)</span>
                                <p class="text-xs text-gray-500 ml-5">You will receive an USSD prompt to authorize payment.</p>
                            </label>
                            
                            <label class="block p-4 border-2 rounded-lg cursor-pointer hover:border-primary-indigo transition duration-150 has-checked:border-primary-indigo has-checked:bg-indigo-50/50">
                                <input type="radio" name="payment_method" value="Credit Card (Stripe)" required class="mr-2 accent-primary-indigo">
                                <span class="font-medium text-gray-800">Credit/Debit Card (Visa/Mastercard)</span>
                                <p class="text-xs text-gray-500 ml-5">Secure payment via Stripe gateway.</p>
                            </label>
                            
                            <label class="block p-4 border-2 rounded-lg cursor-pointer hover:border-primary-indigo transition duration-150 has-checked:border-primary-indigo has-checked:bg-indigo-50/50">
                                <input type="radio" name="payment_method" value="Cash at Station" required class="mr-2 accent-primary-indigo">
                                <span class="font-medium text-gray-800">Cash at Station (Hold Ticket for 1 hour)</span>
                                <p class="text-xs text-red-500 ml-5">Booking will be cancelled if not paid within 60 minutes.</p>
                            </label>
                        </div>
                        
                        <button type="submit"
                                class="w-full py-4 rounded-lg bg-green-600 text-white font-bold text-lg hover:bg-green-700 transition duration-150">
                            Pay <?php echo CURRENCY_SYMBOL; ?><?php echo number_format($booking_data['total_amount'], 0); ?> & Confirm Booking
                        </button>
                    </form>
                </div>
            </div>

        <?php endif; ?>
    </main>
</body>
</html>