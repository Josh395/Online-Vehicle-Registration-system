<?php
include 'config.php';
requireLogin();

$error = '';
$success = '';
$application = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reference = trim($_POST['reference_number']);
    if (empty($reference)) {
        $error = 'Please enter your application reference number.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM applications WHERE reference_number = ? AND user_id = ?");
        $stmt->execute([$reference, $_SESSION['user_id']]);
        $application = $stmt->fetch();
        if (!$application) {
            $error = 'Application not found.';
        } else {
            if (isset($_POST['update_details'])) {
                $fields = [
                    'full_name', 'dob', 'primary_phone', 'email', 'physical_address', 'id_type', 'id_number',
                    'vin', 'make', 'model', 'year', 'vehicle_type', 'color', 'fuel_type',
                    'insurance_provider', 'policy_number'
                ]; 
                $updates = [];
                $values = [];
                foreach ($fields as $field) {
                    $updates[] = "$field = ?";
                    $values[] = isset($_POST[$field]) ? trim($_POST[$field]) : $application[$field];
                }
                $values[] = $application['id'];
                $sql = "UPDATE applications SET " . implode(', ', $updates) . " WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($values);
                
                $stmt = $pdo->prepare("SELECT * FROM applications WHERE id = ?");
                $stmt->execute([$application['id']]);
                $application = $stmt->fetch();
            }
            
            if (isset($_POST['renew'])) {
                $stmt = $pdo->prepare("UPDATE applications SET status = 'submitted' WHERE id = ?");
                $stmt->execute([$application['id']]);
                // Send notification to user
                $user_message = "Your renewal request has been submitted.";
                $notifyStmt = $pdo->prepare("INSERT INTO notifications (user_id, application_id, message) VALUES (?, ?, ?)");
                $notifyStmt->execute([$_SESSION['user_id'], $application['id'], $user_message]);
                $adminStmt = $pdo->prepare("SELECT admin_id FROM admin_users WHERE role = 'SUPER_ADMIN' LIMIT 1");
                $adminStmt->execute();
                $admin = $adminStmt->fetch();
                if ($admin) {
                    $admin_message = "Renewal request submitted for application " . htmlspecialchars($application['reference_number']) . ". Please review and approve.";
                    $notifyStmt = $pdo->prepare("INSERT INTO notifications (user_id, application_id, message) VALUES (?, ?, ?)");
                    $notifyStmt->execute([$admin['admin_id'], $application['id'], $admin_message]);
                }
                header("Location: payment.php?id=" . $application['id']);
                exit;
            }
        }
    }
}
?>
<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="form-container">
        <h2>Renew Application</h2>
        <?php if ($error): ?>
            <div class="alert error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="reference_number">Reference Number</label>
                <input type="text" id="reference_number" name="reference_number" required value="<?php echo isset($_POST['reference_number']) ? htmlspecialchars($_POST['reference_number']) : ''; ?>">
                <button type="submit" class="btn-primary" name="search">Search</button>
            </div>
        </form>
        <?php if ($application): ?>
            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="reference_number" value="<?php echo htmlspecialchars($application['reference_number']); ?>">
                <div class="form-group"><label for="full_name">Full Name</label><input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($application['full_name']); ?>"></div>
                <div class="form-group"><label for="dob">Date of Birth</label><input type="date" id="dob" name="dob" value="<?php echo htmlspecialchars($application['dob']); ?>"></div>
                <div class="form-group"><label for="primary_phone">Primary Phone</label><input type="text" id="primary_phone" name="primary_phone" value="<?php echo htmlspecialchars($application['primary_phone']); ?>"></div>
                <div class="form-group"><label for="email">Email</label><input type="email" id="email" name="email" value="<?php echo htmlspecialchars($application['email']); ?>"></div>
                <div class="form-group"><label for="physical_address">Physical Address</label><input type="text" id="physical_address" name="physical_address" value="<?php echo htmlspecialchars($application['physical_address']); ?>"></div>
                <div class="form-group"><label for="id_type">ID Type</label><input type="text" id="id_type" name="id_type" value="<?php echo htmlspecialchars($application['id_type']); ?>"></div>
                <div class="form-group"><label for="id_number">ID Number</label><input type="text" id="id_number" name="id_number" value="<?php echo htmlspecialchars($application['id_number']); ?>"></div>
                <div class="form-group"><label for="vin">VIN</label><input type="text" id="vin" name="vin" value="<?php echo htmlspecialchars($application['vin']); ?>"></div>
                <div class="form-group"><label for="make">Make</label><input type="text" id="make" name="make" value="<?php echo htmlspecialchars($application['make']); ?>"></div>
                <div class="form-group"><label for="model">Model</label><input type="text" id="model" name="model" value="<?php echo htmlspecialchars($application['model']); ?>"></div>
                <div class="form-group"><label for="year">Year</label><input type="number" id="year" name="year" value="<?php echo htmlspecialchars($application['year']); ?>"></div>
                <div class="form-group"><label for="vehicle_type">Vehicle Type</label><input type="text" id="vehicle_type" name="vehicle_type" value="<?php echo htmlspecialchars($application['vehicle_type']); ?>"></div>
                <div class="form-group"><label for="color">Color</label>
<select id="color" name="color">
    <option value="" disabled>Select color</option>
    <option value="Black" <?php if($application['color']==='Black') echo 'selected'; ?>>Black</option>
    <option value="White" <?php if($application['color']==='White') echo 'selected'; ?>>White</option>
    <option value="Silver" <?php if($application['color']==='Silver') echo 'selected'; ?>>Silver</option>
    <option value="Blue" <?php if($application['color']==='Blue') echo 'selected'; ?>>Blue</option>
    <option value="Red" <?php if($application['color']==='Red') echo 'selected'; ?>>Red</option>
    <option value="Green" <?php if($application['color']==='Green') echo 'selected'; ?>>Green</option>
    <option value="Yellow" <?php if($application['color']==='Yellow') echo 'selected'; ?>>Yellow</option>
    <option value="Grey" <?php if($application['color']==='Grey') echo 'selected'; ?>>Grey</option>
    <option value="Brown" <?php if($application['color']==='Brown') echo 'selected'; ?>>Brown</option>
    <option value="Orange" <?php if($application['color']==='Orange') echo 'selected'; ?>>Orange</option>
    <option value="Purple" <?php if($application['color']==='Purple') echo 'selected'; ?>>Purple</option>
    <option value="Pink" <?php if($application['color']==='Pink') echo 'selected'; ?>>Pink</option>
    <option value="Other" <?php if($application['color']==='Other') echo 'selected'; ?>>Other</option>
</select>
</div>
                <div class="form-group"><label for="fuel_type">Fuel Type</label><input type="text" id="fuel_type" name="fuel_type" value="<?php echo htmlspecialchars($application['fuel_type']); ?>"></div>
                <div class="form-group"><label for="insurance_provider">Insurance Provider</label><input type="text" id="insurance_provider" name="insurance_provider" value="<?php echo htmlspecialchars($application['insurance_provider']); ?>"></div>
                <div class="form-group"><label for="policy_number">Policy Number</label><input type="text" id="policy_number" name="policy_number" value="<?php echo htmlspecialchars($application['policy_number']); ?>"></div>
                <button type="submit" class="btn-secondary" name="update_details">Update Details</button>
                <button type="submit" class="btn-primary" name="renew">Submit Renewal</button>
            </form>
            <div class="application-details">
                <h3>Application Details</h3>
                <p><strong>Reference Number:</strong> <?php echo htmlspecialchars($application['reference_number']); ?></p>
                <p><strong>Vehicle:</strong> <?php echo htmlspecialchars($application['make'] . ' ' . $application['model']); ?></p>
                <p><strong>Status:</strong> <?php echo htmlspecialchars($application['status']); ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
