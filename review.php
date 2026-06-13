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

if (!$application || $application['status'] != 'draft') {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_submit'])) {
    $stmt = $pdo->prepare("UPDATE applications SET status = 'submitted' WHERE id = ?");
    $stmt->execute([$application_id]);
    header("Location: payment.php?id=" . $application_id);
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
            <h1>Review & Confirm Application</h1>
            <span class="status-badge status-draft">Draft</span>
        </div>
        <form method="POST">
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
                    </div>
                </div>
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
                        <?php
                        $shown_types = [];
                        foreach ($uploads as $upload):
                            if (in_array($upload['file_type'], $shown_types)) continue;
                            $shown_types[] = $upload['file_type'];
                        ?>
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
                    <a href="form.php?id=<?php echo $application['id']; ?>" class="btn-secondary">Edit</a>
                    <button type="submit" name="confirm_submit" class="btn-primary">Confirm & Submit</button>
                    <a href="dashboard.php" class="btn-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
