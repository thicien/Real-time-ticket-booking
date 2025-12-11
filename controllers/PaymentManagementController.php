<?php
// controllers/PaymentManagementController.php

require_once __DIR__ . '/../models/Payment.php';

class PaymentManagementController {
    private $paymentModel;

    public function __construct() {
        $this->paymentModel = new Payment();
    }

    /**
     * Retrieves all detailed payments for the management dashboard.
     * @return array
     */
    public function index() {
        return $this->paymentModel->readAll();
    }
    
    /**
     * Handles the manual update of a payment status (for reconciliation).
     * @param int $paymentId
     * @param string $status ('Pending', 'Paid', 'Failed', 'Refunded')
     * @return array Response array (success: bool, message: string)
     */
    public function updatePaymentStatus($paymentId, $status) {
        if (!is_numeric($paymentId)) {
            return ['success' => false, 'message' => "Invalid Payment ID."];
        }

        $validStatuses = ['Pending', 'Paid', 'Failed', 'Refunded'];
        if (!in_array($status, $validStatuses)) {
            return ['success' => false, 'message' => "Invalid status value: " . htmlspecialchars($status)];
        }
        
        if ($this->paymentModel->updateStatus($paymentId, $status)) {
            return ['success' => true, 'message' => "Payment ID {$paymentId} status updated to '{$status}' successfully."];
        } else {
            return ['success' => false, 'message' => "Failed to update payment status. Database error or ID not found."];
        }
    }
}