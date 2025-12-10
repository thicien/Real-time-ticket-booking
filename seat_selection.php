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

// 2. Include Controller
require_once 'controllers/BusController.php';

$schedule_id = $_GET['schedule_id'] ?? null;
$trip = null;
$error_message = '';
$user_name = htmlspecialchars($_SESSION['name']);

// 3. Fetch Trip Data
if (!is_numeric($schedule_id)) {
    $error_message = "Invalid trip selected.";
} else {
    $busController = new BusController();
    $trip = $busController->getScheduleDetailsWithSeats((int)$schedule_id);

    if (!$trip) {
        $error_message = "Trip details not found or an error occurred.";
    }
}

// Helper function to generate seat labels (A1, A2, B1, etc.)
function generate_seat_label($row, $col) {
    $row_letter = chr(65 + $row - 1); // 1 -> A, 2 -> B, etc.
    return $row_letter . $col;
}

// Constants for bus layout rendering (based on your Bus Model fields)
$total_rows = $trip['rows'] ?? 8;      // Default to 8 rows
$total_cols = $trip['columns'] ?? 4;   // Default to 4 seats across (2-aisle-2)
$aisle_col = ceil($total_cols / 2);    // Aisle is after the first half of columns

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seat Selection - BusBook</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary-indigo': '#4f46e5',
                        'seat-available': '#22c55e', // Green
                        'seat-booked': '#ef4444',    // Red
                        'seat-selected': '#3b82f6',  // Blue
                    }
                }
            }
        }
        
        // --- JavaScript for Interactive Seat Selection ---
        function toggleSeat(seatElement, scheduleId, price) {
            const seatNumber = seatElement.dataset.seat;
            const isBooked = seatElement.classList.contains('bg-seat-booked');
            
            if (isBooked) {
                // Cannot select a booked seat
                return;
            }

            seatElement.classList.toggle('bg-seat-selected');
            seatElement.classList.toggle('bg-seat-available');
            
            // Update hidden input field and summary
            updateBookingSummary(seatNumber, price);
        }

        function updateBookingSummary(seatNumber, price) {
            const selectedSeatsInput = document.getElementById('selected_seats');
            let selectedSeats = selectedSeatsInput.value ? selectedSeatsInput.value.split(',') : [];
            
            const isSelected = selectedSeats.includes(seatNumber);

            if (isSelected) {
                // Remove seat
                selectedSeats = selectedSeats.filter(seat => seat !== seatNumber);
            } else {
                // Add seat
                selectedSeats.push(seatNumber);
            }

            selectedSeatsInput.value = selectedSeats.join(',');
            
            // Update summary display
            const seatList = document.getElementById('selected-seat-list');
            seatList.innerHTML = selectedSeats.map(seat => 
                `<span class="inline-block bg-primary-indigo text-white text-xs font-semibold px-2 py-1 rounded-full">${seat}</span>`
            ).join(' ');

            const totalCount = selectedSeats.length;
            const totalPrice = totalCount * price;

            document.getElementById('total-seats-count').textContent = totalCount;
            document.getElementById('total-price').textContent = totalPrice.toLocaleString();

            const proceedButton = document.getElementById('proceed-to-payment');
            if (totalCount > 0) {
                proceedButton.classList.remove('opacity-50', 'cursor-not-allowed');
                proceedButton.disabled = false;
            } else {
                proceedButton.classList.add('opacity-50', 'cursor-not-allowed');
                proceedButton.disabled = true;
            }
        }
    </script>
</head>
<body class="bg-gray-100 min-h-screen">
    
    <main class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        
        <?php if ($error_message): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-8" role="alert">
                <?php echo htmlspecialchars($error_message); ?>
                <p class="mt-2"><a href="user_dashboard.php" class="font-bold underline">Go back to Search</a></p>
            </div>
        <?php elseif ($trip): ?>
            
            <h1 class="text-3xl font-extrabold text-gray-900 mb-2">Select Your Seat</h1>
            <p class="text-gray-600 mb-6">Trip: **<?php echo htmlspecialchars($trip['departure_location']); ?> to <?php echo htmlspecialchars($trip['destination_location']); ?>** at **<?php echo date('H:i', strtotime($trip['departure_time'])); ?>**</p>

            <div class="flex flex-col lg:flex-row gap-8">
                
                <div class="lg:w-3/5 bg-white shadow-xl rounded-xl p-6 md:p-8">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Bus Layout - <?php echo htmlspecialchars($trip['bus_operator']); ?> (<?php echo htmlspecialchars($trip['bus_type']); ?>)</h2>
                    
                    <div class="flex justify-center bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <div class="seat-map-grid inline-block p-4 border-4 border-gray-300 rounded-lg bg-gray-200">
                            
                            <div class="flex justify-between items-center mb-6 px-2">
                                <div class="w-10 h-10 bg-gray-500 rounded-full flex items-center justify-center text-xs text-white">Driver</div>
                                <div class="w-10 h-10 bg-yellow-500 rounded-lg flex items-center justify-center text-xs font-bold text-white">Door</div>
                            </div>
                            
                            <div class="grid gap-2" style="grid-template-columns: repeat(<?php echo $total_cols; ?>, 1fr);">
                                <?php for ($row = 1; $row <= $total_rows; $row++): ?>
                                    <?php for ($col = 1; $col <= $total_cols; $col++): 
                                        $seat_label = generate_seat_label($row, $col);
                                        $is_booked = in_array($seat_label, $trip['booked_seats']);
                                        $seat_class = $is_booked ? 'bg-seat-booked' : 'bg-seat-available cursor-pointer hover:bg-seat-selected/70';
                                        $onclick_attr = $is_booked ? '' : "onclick=\"toggleSeat(this, {$trip['schedule_id']}, {$trip['price']})\"";
                                    ?>
                                        
                                        <?php if ($col == $aisle_col && $col < $total_cols): ?>
                                            <div class="w-full h-10"></div>
                                        <?php endif; ?>
                                        
                                        <div data-seat="<?php echo $seat_label; ?>"
                                             data-schedule-id="<?php echo $trip['schedule_id']; ?>"
                                             class="w-full h-10 <?php echo $seat_class; ?> rounded-md shadow flex items-center justify-center text-xs font-bold text-white transition duration-100 ease-in-out"
                                             <?php echo $onclick_attr; ?>>
                                            <?php echo $seat_label; ?>
                                        </div>
                                        
                                    <?php endfor; ?>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-center gap-6 mt-6 text-sm text-gray-600">
                        <span class="flex items-center"><div class="w-4 h-4 rounded mr-2 bg-seat-available"></div> Available</span>
                        <span class="flex items-center"><div class="w-4 h-4 rounded mr-2 bg-seat-selected"></div> Selected</span>
                        <span class="flex items-center"><div class="w-4 h-4 rounded mr-2 bg-seat-booked"></div> Booked</span>
                    </div>
                </div>

                <div class="lg:w-2/5 bg-white shadow-xl rounded-xl p-6 md:p-8 sticky top-10 h-fit">
                    <h2 class="text-2xl font-semibold text-primary-indigo mb-4 border-b pb-3">Booking Summary</h2>
                    
                    <div class="space-y-4 text-gray-700">
                        <p><strong>Route:</strong> <?php echo htmlspecialchars($trip['departure_location']); ?> &rarr; <?php echo htmlspecialchars($trip['destination_location']); ?></p>
                        <p><strong>Departure:</strong> <?php echo date('l, M j, Y H:i', strtotime($trip['departure_time'])); ?></p>
                        <p><strong>Price per Seat:</strong> **$<?php echo number_format($trip['price'], 0); ?>**</p>
                        <p><strong>Available:</strong> <span class="text-green-600 font-bold"><?php echo $trip['available_seats']; ?> / <?php echo $trip['total_seats']; ?></span></p>
                    </div>

                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h3 class="text-xl font-semibold text-gray-800 mb-3">Your Selection</h3>
                        
                        <div class="min-h-[40px] mb-4 p-2 bg-gray-50 rounded" id="selected-seat-list">
                            <p class="text-sm text-gray-500">No seats selected.</p>
                        </div>

                        <p class="text-lg">Seats: <span id="total-seats-count" class="font-bold text-primary-indigo">0</span></p>
                        <p class="text-2xl font-bold mt-2 text-gray-900">Total Price: $<span id="total-price">0</span></p>
                    </div>
                    
                    <form action="payment.php" method="POST" class="mt-6">
                        <input type="hidden" name="schedule_id" value="<?php echo $trip['schedule_id']; ?>">
                        <input type="hidden" name="price" value="<?php echo $trip['price']; ?>">
                        <input type="hidden" name="selected_seats" id="selected_seats" value="">
                        
                        <button type="submit" id="proceed-to-payment" disabled
                                class="w-full py-3 px-4 rounded-lg bg-primary-indigo text-white font-bold text-lg hover:bg-indigo-700 transition duration-150 opacity-50 cursor-not-allowed">
                            Proceed to Payment
                        </button>
                    </form>
                </div>
            </div>

        <?php endif; ?>
    </main>
</body>
</html>