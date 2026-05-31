<?php
include 'config.php';
requireLogin();

if (!isset($_GET['id'])) {
    echo '<div class="container"><h2>Invalid request</h2></div>';
    exit;
}

$transfer_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM transfer_ownership WHERE id = ? AND user_id = ?");
$stmt->execute([$transfer_id, $_SESSION['user_id']]);
$transfer = $stmt->fetch();

if (!$transfer) {
    echo '<div class="container"><h2>Transfer of Ownership Request Not Found</h2></div>';
    exit;
}

include 'includes/header.php';
?>
<div class="container">
    <h2>Transfer of Ownership Details</h2>
    <div class="details-card">
        <h3>Vehicle: <?php echo htmlspecialchars($transfer['vehicle_reg_number']); ?></h3>
        <p><strong>Status:</strong> <?php echo ucfirst($transfer['status']); ?></p>
        <p><strong>Buyer Name:</strong> <?php echo htmlspecialchars($transfer['buyer_full_name']); ?></p>
        <p><strong>Buyer ID:</strong> <?php echo htmlspecialchars($transfer['buyer_id']); ?></p>
        <p><strong>Buyer DOB:</strong> <?php echo htmlspecialchars($transfer['buyer_dob']); ?></p>
        <p><strong>Buyer Phone:</strong> <?php echo htmlspecialchars($transfer['buyer_phone']); ?></p>
        <p><strong>Buyer Email:</strong> <?php echo htmlspecialchars($transfer['buyer_email']); ?></p>
        <p><strong>Buyer Address:</strong> <?php echo htmlspecialchars($transfer['buyer_address']); ?></p>
        <hr>
        <p><strong>Seller Name:</strong> <?php echo htmlspecialchars($transfer['seller_full_name']); ?></p>
        <p><strong>Seller ID:</strong> <?php echo htmlspecialchars($transfer['seller_id']); ?></p>
        <p><strong>Seller Phone:</strong> <?php echo htmlspecialchars($transfer['seller_phone']); ?></p>
        <p><strong>Seller Email:</strong> <?php echo htmlspecialchars($transfer['seller_email']); ?></p>
        <p><strong>Seller Address:</strong> <?php echo htmlspecialchars($transfer['seller_address']); ?></p>
        <hr>
        <p><strong>Vehicle VIN:</strong> <?php echo htmlspecialchars($transfer['vehicle_vin']); ?></p>
        <p><strong>Engine Number:</strong> <?php echo htmlspecialchars($transfer['engine_number']); ?></p>
        <p><strong>Make:</strong> <?php echo htmlspecialchars($transfer['make']); ?></p>
        <p><strong>Model:</strong> <?php echo htmlspecialchars($transfer['model']); ?></p>
        <p><strong>Year of Manufacture:</strong> <?php echo htmlspecialchars($transfer['year_manufacture']); ?></p>
        <p><strong>Color:</strong> <?php echo htmlspecialchars($transfer['color']); ?></p>
        <p><strong>Odometer:</strong> <?php echo htmlspecialchars($transfer['odometer']); ?></p>
        <hr>
        <p><strong>Submitted:</strong> <?php echo date('M j, Y', strtotime($transfer['created_at'])); ?></p>
        <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($transfer['payment_method']); ?></p>
        <p><strong>Transfer Fee:</strong> <?php echo number_format($transfer['transfer_fee']); ?> TZS</p>
        <?php if ($transfer['status'] == 'rejected' && !empty($transfer['rejection_reason'])): ?>
            <div class="alert error"><strong>Rejection Reason:</strong> <?php echo htmlspecialchars($transfer['rejection_reason']); ?></div>
        <?php endif; ?>
        <?php if ($transfer['status'] == 'approved'): ?>
            <a href="generate-transfer-certificate.php?id=<?php echo $transfer['id']; ?>" class="btn-primary" target="_blank">Download Certificate</a>
        <?php endif; ?>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
