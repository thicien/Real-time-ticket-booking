<?php
// This service file simulates sending an SMS notification.
// In a real application, this is where you would integrate with 
// an SMS API gateway (e.g., Twilio, local API, etc.).

class SmsService {

    /**
     * Simulates sending an SMS message.
     * * @param string $toPhoneNumber The recipient's phone number.
     * @param string $message The content of the SMS.
     * @return bool True if the SMS was "sent" (simulated), false otherwise.
     */
    public static function sendSms($toPhoneNumber, $message) {
        // --- REAL API INTEGRATION GOES HERE ---
        
        // For demonstration, we'll assume it's successful and log it.
        // In a real system, you would call:
        // $client = new Twilio\Rest\Client($sid, $token);
        // $client->messages->create($toPhoneNumber, ['from' => $fromNumber, 'body' => $message]);

        // Simple logging for simulation
        error_log(
            "SMS SENT to: " . $toPhoneNumber . 
            " | Message: " . $message . 
            " | Status: SUCCESS"
        );

        return true; // Simulate success
    }
}
?>