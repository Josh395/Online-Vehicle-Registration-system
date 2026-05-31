<?php
include 'config.php';
requireLogin();

if (!isset($_GET['id'])) {
    header("Location: renewal.php");
    exit;
}

$application_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM applications WHERE id = ? AND user_id = ?");
$stmt->execute([$application_id, $_SESSION['user_id']]);
$application = $stmt->fetch();

if (!$application || $application['status'] != 'submitted') {
    header("Location: dashboard.php");
    exit;
}
if ($application['payment_status'] == 'completed') {
    header("Location: dashboard.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payment_method = $_POST['payment_method'];
    $amount_entered = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
    $required_amount = floatval($application['total_amount']);
    $control_number = 'CN' . time() . rand(100,999);
    if ($amount_entered < $required_amount) {
        $error = 'The amount entered is less than the required amount. Please enter the full amount: TZS ' . number_format($required_amount, 2);
    } else {
        $stmt = $pdo->prepare("INSERT INTO payments (control_number, owner_id, application_id, amount, method, status) VALUES (?, ?, ?, ?, ?, 'Paid')");
        $stmt->execute([$control_number, $_SESSION['user_id'], $application_id, $amount_entered, ucfirst(str_replace('_', '', $payment_method))]);
        $stmt = $pdo->prepare("UPDATE applications SET payment_status = 'completed' WHERE id = ?");
        $stmt->execute([$application_id]);
        $message = 'Payment successful for application ' . $application['reference_number'] . '. Your application is now under review by admin.';
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, application_id, message) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $application_id, $message]);
    $success = 'Payment successful! Your application has been submitted completely!';
    echo '<script>alert("Renewal submitted completely!");</script>';
    echo '<meta http-equiv="refresh" content="3;url=dashboard.php">';
    }
}
?>
<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="payment-container">
        <h1>Payment Processing</h1>
        
        <?php if ($error): ?>
            <div class="alert error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert success"><?php echo $success; ?></div>
            <div class="alert info">Your payment will be verified by an administrator. You will be notified once your application is updated.</div>
        <?php else: ?>
            <div class="payment-details">
                <div class="payment-info">
                    <h2>Application Details</h2>
                    <p><strong>Reference Number:</strong> <?php echo htmlspecialchars($application['reference_number']); ?></p>
                    <p><strong>Vehicle:</strong> <?php echo htmlspecialchars($application['make'] . ' ' . $application['model']); ?></p>
                    <p><strong>Amount Due:</strong> TZS <?php echo number_format($application['total_amount'], 2); ?></p>
                </div>

                <form method="POST" class="payment-form">
                    <h2>Select Payment Method</h2>
                    <div class="payment-methods">
                        <label class="payment-method">
                            <input type="radio" name="payment_method" value="bank" required>
                            <div class="method-content">
                                <h3>Bank Transfer</h3>
                                <p>Transfer money to our bank account</p>
                                <div class="bank-details" style="display: none;">
                                    <p><strong>Bank:</strong> National Bank of Tanzania</p>
                                    <p><strong>Account:</strong> 0123456789</p>
                                    <p><strong>Reference:</strong> <?php echo $application['reference_number']; ?></p>
                                </div>
                            </div>
                        </label>
                        <label class="payment-method">
                            <input type="radio" name="payment_method" value="mobilemoney">
                            <div class="method-content">
                                <h3>Mobile Money</h3>
                                <p>Pay via M-Pesa, Tigo Pesa, or Airtel Money</p>
                                <div class="mobile-details" style="display: none;">
                                    <p>Send payment to: <strong>0757123456</strong></p>
                                    <p>Reference: <strong><?php echo $application['reference_number']; ?></strong></p>
                                </div>
                            </div>
                        </label>
                    </div>
                    <div class="form-group">
                        <label for="amount">Enter Amount (TZS)</label>
                        <input type="number" name="amount" id="amount" min="1" step="1" required>
                        <small>Required: TZS <?php echo number_format($application['total_amount'], 2); ?></small>
                    </div>
                    <button type="submit" class="btn-primary">Make Payment</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
    radio.addEventListener('change', function() {
      
        document.querySelectorAll('.bank-details, .mobile-details, .cash-details').forEach(detail => {
            detail.style.display = 'none';
        });
        
        if (this.value === 'bank_transfer') {
            this.parentElement.querySelector('.bank-details').style.display = 'block';
        } else if (this.value === 'mobile_money') {
            this.parentElement.querySelector('.mobile-details').style.display = 'block';
        } else if (this.value === 'cash') {
            this.parentElement.querySelector('.cash-details').style.display = 'block';
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>