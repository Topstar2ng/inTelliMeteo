<?php
require_once '../includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Ensure the secret key is defined from your environment variables
if (!defined('PAYSTACK_SECRET_KEY')) {
    define('PAYSTACK_SECRET_KEY', isset($_ENV['PAYSTACK_SECRET_KEY']) ? $_ENV['PAYSTACK_SECRET_KEY'] : 'sk_test_your_secret_key_here');
}

// 2. Extract the reference query string parameter sent from the checkout loop
$reference = isset($_GET['reference']) ? trim($_GET['reference']) : '';

if (empty($reference)) {
    die("No transaction reference provided.");
}

// 3. Query Paystack's API endpoint to verify transaction authenticity
$url = "https://api.paystack.co/transaction/verify/" . rawurlencode($reference);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . PAYSTACK_SECRET_KEY,
    "Cache-Control: no-cache"
]);

$response = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

if ($err) {
    die("cURL Error: " . $err);
}

$transaction = json_decode($response, true);

if (!$transaction || !isset($transaction['status']) || !$transaction['status']) {
    die("Verification failed: " . ($transaction['message'] ?? 'Unknown error'));
}

// 4. Validate transaction properties returned by Paystack API
$paymentData = $transaction['data'];

if ($paymentData['status'] === 'success' && $paymentData['currency'] === 'NGN') {
    
    // Paystack returns amounts in Kobo; convert back to Naira for standard logging
    $amountInNaira = $paymentData['amount'] / 100; 
    $donorEmail = $paymentData['customer']['email'];
    
    // Pull the donor name from the custom metadata field defined in the frontend popup setup
    $donorName = 'Guest';
    if (isset($paymentData['metadata']['custom_fields'])) {
        foreach ($paymentData['metadata']['custom_fields'] as $field) {
            if ($field['variable_name'] === 'donor_name') {
                $donorName = $field['value'];
                break;
            }
        }
    }
    
    $paidAt = date("Y-m-d H:i:s", strtotime($paymentData['paid_at']));
    $status = $paymentData['status'];

    try {
        // 5. Check if transaction reference has already been saved to prevent double entries
        $checkStmt = $pdo->prepare("SELECT id FROM server_donations WHERE reference = :ref");
        $checkStmt->execute([':ref' => $reference]);
        
        if ($checkStmt->rowCount() === 0) {
            // Insert the fresh record into the tracking metrics table
            $insertStmt = $pdo->prepare("INSERT INTO server_donations (donor_name, donor_email, amount, reference, status, paid_at) VALUES (:name, :email, :amount, :ref, :status, :paid_at)");
            $insertStmt->execute([
                ':name'    => $donorName,
                ':email'   => $donorEmail,
                ':amount'  => $amountInNaira,
                ':ref'     => $reference,
                ':status'  => $status,
                ':paid_at' => $paidAt
            ]);
            
            $_SESSION['payment_success_message'] = "Thank you, {$donorName}! Your support of ₦" . number_format($amountInNaira, 2) . " has been successfully processed.";
        } else {
            $_SESSION['payment_success_message'] = "Welcome back! This transaction has already been logged.";
        }
        
        // Bounce user securely back home to the dashboard
        header("Location: ../index.php");
        exit();

    } catch (PDOException $e) {
        die("Database system error logging metrics: " . $e->getMessage());
    }

} else {
    // Payment was not successful or failed verification criteria
    die("Transaction verification failed or returned unexpected status: " . htmlspecialchars($paymentData['status']));
}