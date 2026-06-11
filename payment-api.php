<?php
/**
 * Mock Payment Gateway API
 * Simulates a real payment processor for demo purposes
 * Test numbers: amounts 100000+ will simulate failure
 */

include 'config.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['application_id']) || !isset($data['amount']) || !isset($data['payment_method'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$application_id = intval($data['application_id']);
$amount = floatval($data['amount']);
$payment_method = $data['payment_method'];
$user_id = $_SESSION['user_id'];

// Verify application belongs to user
$stmt = $pdo->prepare("SELECT * FROM applications WHERE id = ? AND user_id = ?");
$stmt->execute([$application_id, $user_id]);
$application = $stmt->fetch();

if (!$application) {
    echo json_encode(['success' => false, 'message' => 'Application not found']);
    exit;
}

// Validate amount
if ($amount < $application['total_amount']) {
    echo json_encode(['success' => false, 'message' => 'Amount is less than required']);
    exit;
}

// Simulate payment processing delay (1-2 seconds)
usleep(rand(1000000, 2000000));

// Generate transaction ID
$transaction_id = 'TXN' . time() . rand(10000, 99999);

// Simulate payment validation
// Test: amounts ending in 99999 will fail
$test_fail = ($amount * 100) % 1000000 == 999999;

if ($test_fail) {
    echo json_encode([
        'success' => false,
        'message' => 'Payment declined by bank. Please try again or use a different payment method.',
        'transaction_id' => $transaction_id
    ]);
    exit;
}

// Payment successful - save to database
try {
    $stmt = $pdo->prepare("
        INSERT INTO payments (user_id, application_id, amount, payment_method, transaction_id, status, created_at) 
        VALUES (?, ?, ?, ?, ?, 'completed', NOW())
    ");
    $stmt->execute([$user_id, $application_id, $amount, ucfirst(str_replace('_', ' ', $payment_method)), $transaction_id]);

    // Update application payment status
    $stmt = $pdo->prepare("UPDATE applications SET payment_status = 'completed' WHERE id = ?");
    $stmt->execute([$application_id]);

    // Create notification
    $message = 'Payment successful for application ' . $application['reference_number'] . '. Your application is now under review by admin.';
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, application_id, message, is_read) VALUES (?, ?, ?, 0)");
    $stmt->execute([$user_id, $application_id, $message]);

    echo json_encode([
        'success' => true,
        'message' => 'Payment processed successfully!',
        'transaction_id' => $transaction_id,
        'reference_number' => $application['reference_number']
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Payment recorded but failed to update database: ' . $e->getMessage(),
        'transaction_id' => $transaction_id
    ]);
}
?>
