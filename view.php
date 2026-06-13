<?php
include 'config.php';
requireLogin();

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$application_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM applications WHERE id = ? AND user_id = ?");
$stmt->execute([$application_id, $_SESSION['user_id']]);
$application = $stmt->fetch();

if (!$application) {
    header("Location: dashboard.php");
    exit;
}
$stmt = $pdo->prepare("SELECT * FROM uploads WHERE application_id = ?");
$stmt->execute([$application_id]);
$uploads = $stmt->fetchAll();
?>
<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="application-view">
        <div class="view-header">
            <h1>Application Details</h1>
            <span class="status-badge status-<?php echo $application['status']; ?>">
                <?php echo ucfirst($application['status']); ?>
            </span>
        </div>

        <div class="application-content">
            <div class="application-section">
                <h2>Application Information</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <strong>Reference Number:</strong>
                        <span><?php echo htmlspecialchars($application['reference_number']); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Submission Date:</strong>
                        <span><?php echo date('M j, Y g:i A', strtotime($application['created_at'])); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Status:</strong>
                        <span class="status-badge status-<?php echo $application['status']; ?>">
                            <?php echo ucfirst($application['status']); ?>
                        </span>
                    </div>
                    <?php if ($application['registration_number']): ?>
                    <div class="info-item">
                        <strong>Plate Number:</strong>
                        <span><?php echo htmlspecialchars($application['registration_number']); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($application['payment_status']): ?>
                    <div class="info-item">
                        <strong>Payment Status:</strong>
                        <span class="status-badge status-<?php echo $application['payment_status']; ?>">
                            <?php echo ucfirst($application['payment_status']); ?>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($application['status'] == 'rejected' && $application['rejection_reason']): ?>
            <div class="application-section" style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 20px; margin-bottom: 20px; border-radius: 5px;">
                <h2 style="color: #856404; margin-top: 0;">Rejection Details</h2>
                <p style="color: #856404; margin-bottom: 10px;">Your application was rejected for the following reason:</p>
                <p style="color: #856404; font-weight: 600; padding: 10px; background: rgba(255,255,255,0.5); border-radius: 3px;">
                    <?php echo htmlspecialchars($application['rejection_reason']); ?>
                </p>
                <p style="color: #856404; font-size: 14px; margin-top: 15px;">
                    <a href="form.php?id=<?php echo $application['id']; ?>" style="color: #0c5460; font-weight: 600; text-decoration: none;">← Click here to update your application</a>
                </p>
            </div>
            <?php endif; ?>

            <div class="application-section">
                <h2>Personal Information</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <strong>Full Name:</strong>
                        <span><?php echo htmlspecialchars($application['full_name']); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Date of Birth:</strong>
                        <span><?php echo date('M j, Y', strtotime($application['dob'])); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Phone Number:</strong>
                        <span><?php echo htmlspecialchars($application['primary_phone']); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Email:</strong>
                        <span><?php echo htmlspecialchars($application['email']); ?></span>
                    </div>
                    <div class="info-item full-width">
                        <strong>Address:</strong>
                        <span><?php echo htmlspecialchars($application['physical_address']); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>ID Type:</strong>
                        <span><?php echo ucfirst(str_replace('_', ' ', $application['id_type'])); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>ID Number:</strong>
                        <span><?php echo htmlspecialchars($application['id_number']); ?></span>
                    </div>
                </div>
            </div>

            <div class="application-section">
                <h2>Vehicle Information</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <strong>VIN Number:</strong>
                        <span><?php echo htmlspecialchars($application['vin']); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Make:</strong>
                        <span><?php echo htmlspecialchars($application['make']); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Model:</strong>
                        <span><?php echo htmlspecialchars($application['model']); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Year:</strong>
                        <span><?php echo htmlspecialchars($application['year']); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Vehicle Type:</strong>
                        <span><?php echo ucfirst($application['vehicle_type']); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Color:</strong>
                        <span><?php echo htmlspecialchars($application['color']); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Fuel Type:</strong>
                        <span><?php echo ucfirst($application['fuel_type']); ?></span>
                    </div>
                </div>
            </div>



            <?php if (!empty($uploads)): ?>
            <div class="application-section">
                <h2>Uploaded Documents</h2>
                <div class="documents-grid">
                    <?php foreach ($uploads as $upload): ?>
                        <div class="document-item">
                            <h3><?php echo ucfirst(str_replace('_', ' ', $upload['file_type'])); ?></h3>
                            <a href="<?php echo htmlspecialchars($upload['file_path']); ?>" target="_blank" class="btn-secondary">
                                View Document
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="application-actions">
                <?php if ($application['status'] == 'draft'): ?>
                    <a href="form.php?id=<?php echo $application['id']; ?>" class="btn-primary">Edit Application</a>
                <?php elseif ($application['status'] == 'submitted' && $application['payment_status'] == 'pending'): ?>
                    <a href="payment.php?id=<?php echo $application['id']; ?>" class="btn-primary">Make Payment</a>
                <?php elseif ($application['status'] == 'approved'): ?>
                    <a href="generate-pdf.php?id=<?php echo $application['id']; ?>" class="btn-primary" target="_blank">Download Certificate</a>
                <?php endif; ?>
                <a href="dashboard.php" class="btn-secondary">Back to Dashboard</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>