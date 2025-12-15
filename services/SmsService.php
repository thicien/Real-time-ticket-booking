<?php

class SmsService {

    /**
     * Simulates sending an SMS message.
     * * @param string $toPhoneNumber The recipient's phone number.
     * @param string $message The content of the SMS.
     * @return bool True if the SMS was "sent" (simulated), false otherwise.
     */
    public static function sendSms($toPhoneNumber, $message) {
        error_log(
            "SMS SENT to: " . $toPhoneNumber . 
            " | Message: " . $message . 
            " | Status: SUCCESS"
        );

        return true; 
    }
}
?>