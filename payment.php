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
?>
<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="payment-container">
        <h1>Payment Processing</h1>
        
        <div id="payment-result" style="display: none;">
            <div id="payment-message"></div>
            <div id="transaction-info" style="margin-top: 20px; padding: 15px; background: #f5f5f5; border-radius: 5px; display: none;">
                <p><strong>Transaction ID:</strong> <span id="txn-id"></span></p>
                <p><strong>Reference Number:</strong> <span id="ref-number"></span></p>
                <p><strong>Amount Paid:</strong> <span id="txn-amount"></span></p>
                <p style="margin-top: 15px; font-size: 14px; color: #666;">Your payment will be verified by an administrator. You will be notified once your application is updated.</p>
                <a href="dashboard.php" class="btn-primary" style="margin-top: 15px; display: inline-block;">Go to Dashboard</a>
            </div>
        </div>

        <div id="payment-form-section">
            <div class="payment-details">
                <div class="payment-info">
                    <h2>Application Details</h2>
                    <p><strong>Reference Number:</strong> <?php echo htmlspecialchars($application['reference_number']); ?></p>
                    <p><strong>Vehicle:</strong> <?php echo htmlspecialchars($application['make'] . ' ' . $application['model']); ?></p>
                    <p><strong>Amount Due:</strong> TZS <?php echo number_format($application['total_amount'], 2); ?></p>
                </div>

                <form id="payment-form" class="payment-form">
                    <h2>Select Payment Method</h2>
                    <div class="payment-methods">
                        <label class="payment-method">
                            <input type="radio" name="payment_method" value="bank_transfer" required>
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
                            <input type="radio" name="payment_method" value="mobile_money" required>
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
                        <input type="number" name="amount" id="amount" min="<?php echo $application['total_amount']; ?>" step="1" required>
                        <small>Required: TZS <?php echo number_format($application['total_amount'], 2); ?></small>
                    </div>
                    <button type="submit" class="btn-primary" id="pay-btn">Make Payment</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Payment Processing Modal -->
<div id="payment-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center;">
    <div style="background: white; padding: 40px; border-radius: 10px; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <div style="margin-bottom: 20px;">
            <div style="font-size: 48px; margin-bottom: 20px;">⏳</div>
            <h2>Processing Payment...</h2>
            <p>Please wait while we process your payment.</p>
        </div>
        <div style="width: 50px; height: 50px; border: 4px solid #ddd; border-top: 4px solid #007bff; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto;"></div>
        <style>
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        </style>
    </div>
</div>

<script>
document.getElementById('payment-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // Hide form, show modal
    document.getElementById('payment-form-section').style.display = 'none';
    document.getElementById('payment-modal').style.display = 'flex';
    
    const formData = new FormData(this);
    const paymentData = {
        application_id: <?php echo $application_id; ?>,
        amount: parseFloat(formData.get('amount')),
        payment_method: formData.get('payment_method')
    };
    
    try {
        const response = await fetch('payment-api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(paymentData)
        });
        
        const result = await response.json();
        
        // Hide modal
        document.getElementById('payment-modal').style.display = 'none';
        
        // Show result
        const resultDiv = document.getElementById('payment-result');
        const messageDiv = document.getElementById('payment-message');
        const infoDiv = document.getElementById('transaction-info');
        
        if (result.success) {
            messageDiv.innerHTML = '<div class="alert success">✓ ' + result.message + '</div>';
            document.getElementById('txn-id').textContent = result.transaction_id;
            document.getElementById('ref-number').textContent = result.reference_number;
            document.getElementById('txn-amount').textContent = 'TZS ' + paymentData.amount.toLocaleString('en-TZ');
            infoDiv.style.display = 'block';
        } else {
            messageDiv.innerHTML = '<div class="alert error">✗ ' + result.message + '</div>';
            if (result.transaction_id) {
                messageDiv.innerHTML += '<p style="margin-top: 10px; font-size: 12px; color: #666;">Transaction ID: ' + result.transaction_id + '</p>';
            }
            messageDiv.innerHTML += '<a href="javascript:location.reload()" class="btn-secondary" style="margin-top: 15px; display: inline-block;">Try Again</a>';
            infoDiv.style.display = 'none';
        }
        
        resultDiv.style.display = 'block';
        
    } catch (error) {
        document.getElementById('payment-modal').style.display = 'none';
        const messageDiv = document.getElementById('payment-message');
        messageDiv.innerHTML = '<div class="alert error">Network error: ' + error.message + '</div>';
        document.getElementById('payment-result').style.display = 'block';
    }
});

// Show payment method details
document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.bank-details, .mobile-details').forEach(detail => {
            detail.style.display = 'none';
        });
        
        if (this.value === 'bank_transfer') {
            this.closest('.payment-method').querySelector('.bank-details').style.display = 'block';
        } else if (this.value === 'mobile_money') {
            this.closest('.payment-method').querySelector('.mobile-details').style.display = 'block';
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>