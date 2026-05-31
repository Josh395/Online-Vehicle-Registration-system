<?php
include 'config.php';
requireLogin();

$error = '';
$success = '';

function upload_doc($input_name, $allowed_exts = ['pdf','jpg','jpeg','png']) {
    if (!isset($_FILES[$input_name]) || $_FILES[$input_name]['error'] !== UPLOAD_ERR_OK) return '';
    $ext = strtolower(pathinfo($_FILES[$input_name]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_exts)) return '';
    $dir = 'uploads/transfer_docs/';
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    $fname = uniqid($input_name.'_') . '.' . $ext;
    $fpath = $dir . $fname;
    if (move_uploaded_file($_FILES[$input_name]['tmp_name'], $fpath)) return $fpath;
    return '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $required = [
        'seller_full_name','seller_id','seller_phone','seller_email','seller_address',
        'buyer_full_name','buyer_id','buyer_dob','buyer_phone','buyer_email','buyer_address',
        'vehicle_reg_number','vehicle_vin','engine_number','make','model','year_manufacture','color','odometer',
        'payment_method'
    ];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $error = 'All fields are required.';
            break;
        }
    }
    // Validate emails
    if (!$error && (!filter_var($_POST['seller_email'], FILTER_VALIDATE_EMAIL) || !filter_var($_POST['buyer_email'], FILTER_VALIDATE_EMAIL))) {
        $error = 'Invalid email address.';
    }
    // Validate seller NIN/Passport
    if (!$error) {
        $seller_id = $_POST['seller_id'];
        $seller_full_name = $_POST['seller_full_name'];
        $stmt = $pdo->prepare("SELECT nin FROM valid_nins WHERE nin = ? AND name = ?");
        $stmt->execute([$seller_id, $seller_full_name]);
        if ($stmt->rowCount() == 0) {
            $stmt = $pdo->prepare("SELECT passport FROM valid_passports WHERE passport = ? AND name = ?");
            $stmt->execute([$seller_id, $seller_full_name]);
            if ($stmt->rowCount() == 0) {
                $error = 'Seller NIN or Passport does not match the name.';
            }
        }
    }
    // Validate buyer NIN/Passport
    if (!$error) {
        $buyer_id = $_POST['buyer_id'];
        $buyer_full_name = $_POST['buyer_full_name'];
        $stmt = $pdo->prepare("SELECT nin FROM valid_nins WHERE nin = ? AND name = ?");
        $stmt->execute([$buyer_id, $buyer_full_name]);
        if ($stmt->rowCount() == 0) {
            $stmt = $pdo->prepare("SELECT passport FROM valid_passports WHERE passport = ? AND name = ?");
            $stmt->execute([$buyer_id, $buyer_full_name]);
            if ($stmt->rowCount() == 0) {
                $error = 'Buyer NIN or Passport does not match the name.';
            }
        }
    }
    if (!$error) {
        $sale_agreement = upload_doc('sale_agreement');
        $prev_reg_card = upload_doc('prev_reg_card');
        if (!$sale_agreement || !$prev_reg_card) {
            $error = 'All supporting documents must be uploaded (PDF, JPG, JPEG, PNG).';
        }
    }
    // Set transfer fee based on vehicle type
    $transfer_fee = 0;
    if (isset($_POST['make']) && isset($_POST['model'])) {
        $make = strtolower($_POST['make']);
        $model = strtolower($_POST['model']);
        if (strpos($make, 'motorcycle') !== false || strpos($model, 'motorcycle') !== false || strpos($make, 'boda') !== false || strpos($model, 'boda') !== false) {
            $transfer_fee = 27000;
        } else {
            $transfer_fee = 50000;
        }
    }
    if (!$error) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("INSERT INTO transfer_ownership (
                seller_full_name, seller_id, seller_phone, seller_email, seller_address,
                buyer_full_name, buyer_id, buyer_dob, buyer_phone, buyer_email, buyer_address,
                vehicle_reg_number, vehicle_vin, engine_number, make, model, year_manufacture, color, odometer,
                sale_agreement, prev_reg_card, transfer_fee, payment_method, user_id, created_at
            ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, NOW())");
            $stmt->execute([
                $_POST['seller_full_name'], $_POST['seller_id'], $_POST['seller_phone'], $_POST['seller_email'], $_POST['seller_address'],
                $_POST['buyer_full_name'], $_POST['buyer_id'], $_POST['buyer_dob'], $_POST['buyer_phone'], $_POST['buyer_email'], $_POST['buyer_address'],
                $_POST['vehicle_reg_number'], $_POST['vehicle_vin'], $_POST['engine_number'], $_POST['make'], $_POST['model'], $_POST['year_manufacture'], $_POST['color'], $_POST['odometer'],
                $sale_agreement, $prev_reg_card, $transfer_fee, $_POST['payment_method'], $_SESSION['user_id']
            ]);
            $notify = $pdo->prepare("INSERT INTO notifications (user_id, application_id, message, is_read, created_at) VALUES (?, NULL, ?, 0, NOW())");
            $notify->execute([
                $_SESSION['user_id'],
                'Your transfer of ownership request has been submitted and is awaiting approval.'
            ]);
            // Send notification (email or message)
            if (!empty($_SESSION['user_email'])) {
                mail($_SESSION['user_email'], 'Transfer of Ownership Submitted', 'Your transfer of ownership request has been submitted and is awaiting approval.');
            }
            header('Location: dashboard.php');
            exit;
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

include 'includes/header.php';
?>
<div class="container">
    <div class="form-container">
        <h2>Transfer Vehicle Ownership</h2>
        <?php if ($error): ?>
            <div class="alert error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="POST" action="" enctype="multipart/form-data">
            <h3>1. Seller Details</h3>
            <div class="form-group"><label>Full Name</label><input type="text" name="seller_full_name" required value="<?php echo isset($_POST['seller_full_name']) ? htmlspecialchars($_POST['seller_full_name']) : ''; ?>"></div>
            <div class="form-group"><label>NIN or Passport Number</label><input type="text" name="seller_id" required value="<?php echo isset($_POST['seller_id']) ? htmlspecialchars($_POST['seller_id']) : ''; ?>"></div>
            <div class="form-group"><label>Phone</label><input type="text" name="seller_phone" required value="<?php echo isset($_POST['seller_phone']) ? htmlspecialchars($_POST['seller_phone']) : ''; ?>"></div>
            <div class="form-group"><label>Email</label><input type="email" name="seller_email" required value="<?php echo isset($_POST['seller_email']) ? htmlspecialchars($_POST['seller_email']) : ''; ?>"></div>
            <div class="form-group"><label>Address</label><input type="text" name="seller_address" required value="<?php echo isset($_POST['seller_address']) ? htmlspecialchars($_POST['seller_address']) : ''; ?>"></div>

            <h3>2. Buyer Details</h3>
            <div class="form-group"><label>Full Name</label><input type="text" name="buyer_full_name" required value="<?php echo isset($_POST['buyer_full_name']) ? htmlspecialchars($_POST['buyer_full_name']) : ''; ?>"></div>
            <div class="form-group"><label>NIN or Passport Number</label><input type="text" name="buyer_id" required value="<?php echo isset($_POST['buyer_id']) ? htmlspecialchars($_POST['buyer_id']) : ''; ?>"></div>
            <div class="form-group"><label>Date of Birth</label><input type="date" name="buyer_dob" required value="<?php echo isset($_POST['buyer_dob']) ? htmlspecialchars($_POST['buyer_dob']) : ''; ?>"></div>
            <div class="form-group"><label>Phone</label><input type="text" name="buyer_phone" required value="<?php echo isset($_POST['buyer_phone']) ? htmlspecialchars($_POST['buyer_phone']) : ''; ?>"></div>
            <div class="form-group"><label>Email</label><input type="email" name="buyer_email" required value="<?php echo isset($_POST['buyer_email']) ? htmlspecialchars($_POST['buyer_email']) : ''; ?>"></div>
            <div class="form-group"><label>Address</label><input type="text" name="buyer_address" required value="<?php echo isset($_POST['buyer_address']) ? htmlspecialchars($_POST['buyer_address']) : ''; ?>"></div>

            <h3>3. Vehicle Details</h3>
            <div class="form-group"><label>Vehicle Registration Number (Plate Number)</label><input type="text" name="vehicle_reg_number" required value="<?php echo isset($_POST['vehicle_reg_number']) ? htmlspecialchars($_POST['vehicle_reg_number']) : ''; ?>"></div>
            <div class="form-group"><label>VIN / Chassis Number</label><input type="text" name="vehicle_vin" required value="<?php echo isset($_POST['vehicle_vin']) ? htmlspecialchars($_POST['vehicle_vin']) : ''; ?>"></div>
            <div class="form-group"><label>Engine Number</label><input type="text" name="engine_number" required value="<?php echo isset($_POST['engine_number']) ? htmlspecialchars($_POST['engine_number']) : ''; ?>"></div>
            <div class="form-group"><label>Vehicle Make</label><input type="text" name="make" required value="<?php echo isset($_POST['make']) ? htmlspecialchars($_POST['make']) : ''; ?>" id="make_input"></div>
            <div class="form-group"><label>Vehicle Model</label><input type="text" name="model" required value="<?php echo isset($_POST['model']) ? htmlspecialchars($_POST['model']) : ''; ?>" id="model_input"></div>
            <div class="form-group"><label>Year of Manufacture</label><input type="number" name="year_manufacture" required value="<?php echo isset($_POST['year_manufacture']) ? htmlspecialchars($_POST['year_manufacture']) : ''; ?>"></div>
            <div class="form-group"><label>Color</label><input type="text" name="color" required value="<?php echo isset($_POST['color']) ? htmlspecialchars($_POST['color']) : ''; ?>"></div>
            <div class="form-group"><label>Current Odometer Reading</label><input type="number" name="odometer" required value="<?php echo isset($_POST['odometer']) ? htmlspecialchars($_POST['odometer']) : ''; ?>"></div>

            <h3>4. Supporting Documents</h3>
            <div class="form-group"><label>Proof of Sale Agreement</label><input type="file" name="sale_agreement" accept=".pdf,.jpg,.jpeg,.png" required></div>
            <div class="form-group"><label>Previous Registration Card / Certificate</label><input type="file" name="prev_reg_card" accept=".pdf,.jpg,.jpeg,.png" required></div>

            <h3>5. Payment Details</h3>
            <div class="form-group"><label>Transfer Fee</label><input type="number" name="transfer_fee" id="transfer_fee" value="<?php echo isset($transfer_fee) ? $transfer_fee : ''; ?>" readonly required></div>
            <div class="form-group"><label>Payment Method</label>
                <select name="payment_method" required>
                    <option value="">Select</option>
                    <option value="mobile" <?php if(isset($_POST['payment_method']) && $_POST['payment_method']==='mobile') echo 'selected'; ?>>Mobile Money</option>
                    <option value="bank" <?php if(isset($_POST['payment_method']) && $_POST['payment_method']==='bank') echo 'selected'; ?>>Bank</option>
                    <option value="card" <?php if(isset($_POST['payment_method']) && $_POST['payment_method']==='card') echo 'selected'; ?>>Card</option>
                </select>
            </div>
            <button type="submit" class="btn-primary">Submit Transfer</button>
        </form>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var makeInput = document.getElementById('make_input');
            var modelInput = document.getElementById('model_input');
            var feeInput = document.getElementById('transfer_fee');
            function updateFee() {
                var make = makeInput.value.toLowerCase();
                var model = modelInput.value.toLowerCase();
                var fee = (make.includes('motorcycle') || model.includes('motorcycle') || make.includes('boda') || model.includes('boda')) ? 27000 : 50000;
                feeInput.value = fee;
            }
            if(makeInput && modelInput && feeInput) {
                makeInput.addEventListener('input', updateFee);
                modelInput.addEventListener('input', updateFee);
            }
        });
        </script>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
