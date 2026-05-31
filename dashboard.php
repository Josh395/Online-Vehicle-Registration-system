<?php
// dashboard.php
include 'config.php';
requireLogin();

// Get user applications
$stmt = $pdo->prepare("SELECT * FROM applications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$applications = $stmt->fetchAll();

// Get notifications
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$_SESSION['user_id']]);
$notifications = $stmt->fetchAll();
?>

<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="dashboard-header">
        <h1>Dashboard</h1>
        <p>Welcome,
        <?php
        if (!empty($_SESSION['user_name'])) {
            echo htmlspecialchars($_SESSION['user_name']);
        } else {
            // Fallback: fetch name from valid_tins using user's TIN
            if (!empty($_SESSION['user_id'])) {
                $stmt = $pdo->prepare("SELECT v.name FROM users u JOIN valid_tins v ON u.tin = v.tin WHERE u.user_id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $row = $stmt->fetch();
                echo htmlspecialchars($row ? $row['name'] : 'User');
            } else {
                echo 'User';
            }
        }
        ?>
        </p>
    </div>

    <?php if (!empty($notifications)): ?>
    <div class="notifications">
        <h3>Notifications</h3>
        <?php foreach ($notifications as $notification): ?>
            <div class="notification">
                <p><?php echo htmlspecialchars($notification['message']); ?></p>
                <small><?php echo date('M j, Y g:i A', strtotime($notification['created_at'])); ?></small>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="applications-section">
        <div class="section-header">
            <h2>Your Applications</h2>
        </div>

        <?php
        // Fetch user's transfer ownership requests
        $stmt = $pdo->prepare("SELECT *, 'transfer' AS app_type FROM transfer_ownership WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$_SESSION['user_id']]);
        $transfers = $stmt->fetchAll();

        // Mark all applications as 'application' type for merging
        foreach ($applications as &$app) {
            $app['app_type'] = 'application';
        }
        unset($app);

        // Merge and remove duplicates by unique key (app_type + id)
        $all_items_assoc = [];
        foreach ($applications as $app) {
            $all_items_assoc['application_' . $app['id']] = $app;
        }
        foreach ($transfers as $transfer) {
            $all_items_assoc['transfer_' . $transfer['id']] = $transfer;
        }
        $all_items = array_values($all_items_assoc);
        usort($all_items, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        ?>

        <?php if (empty($all_items)): ?>
            <div class="empty-state">
                <p>You haven't submitted any applications yet.</p>
                <a href="form.php" class="btn-primary">Start Your First Application</a>
            </div>
        <?php else: ?>
            <div class="applications-grid">
                <?php foreach ($all_items as $item): ?>
                    <div class="application-card">
                        <div class="app-header">
                            <?php if ($item['app_type'] === 'application'): ?>
                                <h3><?php echo htmlspecialchars($item['reference_number']); ?></h3>
                                <span class="status-badge status-<?php echo $item['status']; ?>">
                                    <?php echo ucfirst($item['status']); ?>
                                </span>
                            <?php else: ?>
                                <h3><?php echo htmlspecialchars($item['vehicle_reg_number']); ?> (Transfer)</h3>
                                <span class="status-badge status-<?php echo $item['status']; ?>">
                                    <?php echo ucfirst($item['status']); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="app-details">
                            <?php if ($item['app_type'] === 'application'): ?>
                                <p><strong>Vehicle:</strong> <?php echo htmlspecialchars($item['make'] . ' ' . $item['model']); ?></p>
                                <p><strong>Submitted:</strong> <?php echo date('M j, Y', strtotime($item['created_at'])); ?></p>
                                <?php if ($item['registration_number']): ?>
                                    <p><strong>Plate Number:</strong> <?php echo htmlspecialchars($item['registration_number']); ?></p>
                                <?php endif; ?>
                            <?php else: ?>
                                <p><strong>Buyer:</strong> <?php echo htmlspecialchars($item['buyer_full_name']); ?></p>
                                <p><strong>Submitted:</strong> <?php echo date('M j, Y', strtotime($item['created_at'])); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="app-actions">
                            <?php if ($item['app_type'] === 'application'): ?>
                                <a href="view.php?id=<?php echo $item['id']; ?>" class="btn-secondary">View Details</a>
                                <?php if ($item['status'] == 'draft'): ?>
                                    <a href="form.php?id=<?php echo $item['id']; ?>" class="btn-secondary">Continue Editing</a>
                                <?php elseif ($item['status'] == 'submitted' && $item['payment_status'] == 'pending'): ?>
                                    <a href="payment.php?id=<?php echo $item['id']; ?>" class="btn-primary">Make Payment</a>
                                <?php elseif ($item['status'] == 'approved'): ?>
                                    <a href="generate-pdf.php?id=<?php echo $item['id']; ?>" class="btn-primary" target="_blank">Download Certificate</a>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="view-transfer-ownership.php?id=<?php echo $item['id']; ?>" class="btn-secondary">View Details</a>
                                <?php if ($item['status'] == 'approved'): ?>
                                    <a href="generate-transfer-certificate.php?id=<?php echo $item['id']; ?>" class="btn-primary" target="_blank">Download Certificate</a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>



<?php include 'includes/footer.php'; ?>