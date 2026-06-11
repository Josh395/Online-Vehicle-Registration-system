<?php
// form.php
include 'config.php';
requireLogin();

$edit_mode = false;
$application = null;
$error = '';
$success = '';

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM applications WHERE id = ? AND user_id = ?");
    $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
    $application = $stmt->fetch();
    
    if ($application) {
        $edit_mode = true;
        if ($application['status'] != 'draft') {
            header("Location: dashboard.php");
            exit;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Fetch user's TIN and email
    $user_id = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
    $stmt = $pdo->prepare("SELECT tin, email, phone FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        $error = 'Unable to save application because your user account was not found. Please log out and log in again.';
    }

    $nin_search = isset($_POST['nin_search']) ? trim($_POST['nin_search']) : '';
    $full_name = '';
    $id_type = '';
    $id_number = '';
    $email = '';
    $primary_phone = '';
    $dob = '';
    $physical_address = '';
    $tin = $user ? $user['tin'] : '';

    if ($nin_search !== '') {
        if (!preg_match('/^[0-9]{20}$/', $nin_search)) {
            $error = 'NIN must be exactly 20 digits.';
        } else {
            $stmt = $pdo->prepare("SELECT name, tin, dob, physical_address FROM valid_nins WHERE nin = ?");
            $stmt->execute([$nin_search]);
            $nin_record = $stmt->fetch();

            if (!$nin_record) {
                $error = 'NIN not found in the database.';
            } elseif ($user && $nin_record['tin'] !== $user['tin']) {
                $error = 'The NIN entered does not match your registered TIN.';
            } else {
                $full_name = $nin_record['name'];
                $tin = $nin_record['tin'];
                $id_type = 'national_id';
                $id_number = $nin_search;
                $dob = $nin_record['dob'] ?? '';
                $physical_address = $nin_record['physical_address'] ?? '';
                $email = $user['email'];
                $primary_phone = $user['phone'];
            }
        }
    } else {
        $full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
        $id_type = isset($_POST['id_type']) ? $_POST['id_type'] : '';
        $id_number = isset($_POST['id_number']) ? $_POST['id_number'] : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $primary_phone = isset($_POST['primary_phone']) ? trim($_POST['primary_phone']) : '';
        $dob = isset($_POST['dob']) ? $_POST['dob'] : '';
        $physical_address = isset($_POST['physical_address']) ? trim($_POST['physical_address']) : '';
    }
    // check for valid NIN/Passport matches name and TIN
    if (!$error) {
        if ($id_type === 'national_id') {
            $stmt = $pdo->prepare("SELECT nin FROM valid_nins WHERE nin = ? AND name = ? AND tin = ?");
            $stmt->execute([$id_number, $full_name, $tin]);
            if ($stmt->rowCount() == 0) {
                $error = 'NIDA (NIN) does not match your name and TIN.';
            }
        } elseif ($id_type === 'passport') {
            $stmt = $pdo->prepare("SELECT passport FROM valid_passports WHERE passport = ? AND name = ? AND tin = ?");
            $stmt->execute([$id_number, $full_name, $tin]);
            if ($stmt->rowCount() == 0) {
                $error = 'Passport does not match your name and TIN.';
            }
        }
    }
    if (!empty($error)) {
        // Do not proceed when validation failed or the user account is invalid.
    } else {
        $vehicle_type = $_POST['vehicle_type'];
        $amount = ($vehicle_type === 'motorcycle') ? 95000 : 250000;
        $data = [
            'user_id' => $user_id,
            'full_name' => $full_name,
            'dob' => $_POST['dob'],
                'primary_phone' => $primary_phone ?: $_POST['primary_phone'],
                'email' => $email ?: $_POST['email'],
            'physical_address' => $_POST['physical_address'],
            'id_type' => $id_type,
            'id_number' => $id_number,
            'vin' => $_POST['vin'],
            'make' => $_POST['make'],
            'model' => $_POST['model'],
            'year' => $_POST['year'],
            'vehicle_type' => $vehicle_type,
            'color' => $_POST['color'],
            'fuel_type' => $_POST['fuel_type'],
            'insurance_provider' => $_POST['insurance_provider'],
            'policy_number' => $_POST['policy_number'],
            'total_amount' => $amount
        ];

        try {
            if ($edit_mode) {
                // Update application
                $sql = "UPDATE applications SET " . implode(', ', array_map(fn($k) => "$k = ?", array_keys($data))) . " WHERE id = ?";
                $params = array_values($data);
                $params[] = $application['id'];
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $application_id = $application['id'];
            } else {
                // Create new application
                $reference = 'APP-' . date('Ymd') . '-' . rand(1000, 9999);
                $data['reference_number'] = $reference;
                $data['status'] = 'draft';
                
                $columns = implode(', ', array_keys($data));
                $placeholders = implode(', ', array_fill(0, count($data), '?'));
                $sql = "INSERT INTO applications ($columns) VALUES ($placeholders)";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute(array_values($data));
                $application_id = $pdo->lastInsertId();
            }

            // to handle file uploads
            if (!empty($_FILES)) {
                $upload_dir = "uploads/$application_id/";
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $file_types = ['vehicle_photo'];
                foreach ($file_types as $file_type) {
                    if (isset($_FILES[$file_type]) && $_FILES[$file_type]['error'] == 0) {
                        $file = $_FILES[$file_type];
                        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                        $filename = $file_type . '_' . time() . '.' . $extension;
                        $filepath = $upload_dir . $filename;
                        
                        if (move_uploaded_file($file['tmp_name'], $filepath)) {
                            // Save file info to database
                            $stmt = $pdo->prepare("INSERT INTO uploads (application_id, file_type, file_path) VALUES (?, ?, ?)");
                            $stmt->execute([$application_id, $file_type, $filepath]);
                        }
                    }
                }
            }

            if (isset($_POST['action']) && $_POST['action'] == 'submit') {
                // Save as draft
                header("Location: review.php?id=" . $application_id);
                exit;
            } else {
                $success = 'Application saved as draft.';
            }
        } catch (PDOException $e) {
            $error = 'Error saving application: ' . $e->getMessage();
        }
    }
}
?>
<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="form-header">
        <h1><?php echo $edit_mode ? 'Edit Application' : 'New Vehicle Registration'; ?></h1>
        <?php if ($edit_mode): ?>
            <p>Reference: <?php echo htmlspecialchars($application['reference_number']); ?></p>
        <?php endif; ?>
    </div>

    <?php if ($error): ?>
        <div class="alert error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="vehicle-form">
        <input type="hidden" name="action" id="form_action" value="save">

        <div class="form-section">
            <h2>Personal Information</h2>
                <div class="form-group">
                    <label for="nin_search">Enter NIDA (NIN) to lookup your details</label>
                    <div style="display:flex; gap:8px; align-items:center;">
                        <input type="text" id="nin_search" name="nin_search" pattern="[0-9]{20}" maxlength="20" placeholder="Enter 20-digit NIN" style="flex:1;" value="<?php echo isset($nin_search) ? htmlspecialchars($nin_search) : ''; ?>" />
                        <button type="button" id="lookup_nin" class="btn-secondary">Lookup</button>
                    </div>
                    <small>Click "Lookup" to fetch name, email and phone from our records.</small>
                </div>
                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <input type="text" id="full_name" name="full_name" required readonly value="<?php echo $edit_mode ? htmlspecialchars($application['full_name']) : (isset($full_name) ? htmlspecialchars($full_name) : ''); ?>">
                </div>
                <div class="form-group">
                    <label for="dob">Date of Birth *</label>
                    <input type="date" id="dob" name="dob" required <?php echo $edit_mode ? '' : 'readonly'; ?> value="<?php echo $edit_mode ? htmlspecialchars($application['dob']) : (isset($dob) ? htmlspecialchars($dob) : ''); ?>">
                </div>
                <div class="form-group">
                    <label for="id_type">ID Type *</label>
                    <?php if ($edit_mode): ?>
                        <select id="id_type" name="id_type" required onchange="updateIdNumberField()">
                            <option value="">Select ID Type</option>
                            <option value="national_id" <?php echo ($edit_mode && $application['id_type'] == 'national_id') ? 'selected' : ''; ?>>NIDA (NIN)</option>
                            <option value="passport" <?php echo ($edit_mode && $application['id_type'] == 'passport') ? 'selected' : ''; ?>>Passport</option>
                        </select>
                    <?php else: ?>
                        <input type="text" id="id_type_display" readonly value="NIDA (NIN)">
                        <input type="hidden" id="id_type" name="id_type" value="national_id">
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="id_number">ID Number *</label>
                    <input type="text" id="id_number" name="id_number" required <?php echo $edit_mode ? '' : 'readonly'; ?>
                        pattern="[0-9]{20}"
                        minlength="20" maxlength="20"
                        title="Enter a valid NIDA (20 digits) or Passport (2 letters + 7 digits) number"
                        placeholder=""
                        value="<?php echo $edit_mode ? htmlspecialchars($application['id_number']) : (isset($id_number) ? htmlspecialchars($id_number) : ''); ?>">
                </div>
                <div class="form-group full-width">
                    <label for="physical_address">Residential Address *</label>
                    <input type="text" id="physical_address" name="physical_address" required <?php echo $edit_mode ? '' : 'readonly'; ?> pattern="[A-Za-z0-9\s,.'-]+" title="Enter a valid address (letters, numbers, spaces, comma, dot, apostrophe, dash)" placeholder="e.g. 123 Main St, P.O. Box 456, City" value="<?php echo $edit_mode ? htmlspecialchars($application['physical_address']) : (isset($physical_address) ? htmlspecialchars($physical_address) : ''); ?>">
                </div>
                <div class="form-group">
                    <label for="primary_phone">Phone Number *</label>
                    <input type="tel" id="primary_phone" name="primary_phone" required readonly pattern="\+?[0-9]{10,15}" title="Phone number is loaded from your account" placeholder="e.g. 0712345678" value="<?php echo $edit_mode ? htmlspecialchars($application['primary_phone']) : (isset($primary_phone) ? htmlspecialchars($primary_phone) : ''); ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" required readonly placeholder="e.g. joshalexander@gmail.com" value="<?php echo $edit_mode ? htmlspecialchars($application['email']) : (isset($email) ? htmlspecialchars($email) : ''); ?>">
                </div>
        </div>
<script>
function updateIdNumberField() {
    var idType = document.getElementById('id_type').value;
    var idNumber = document.getElementById('id_number');
    if (idType === 'passport') {
        idNumber.pattern = '[A-Za-z]{2}[0-9]{7}';
        idNumber.title = 'Passport number must be 2 letters followed by 7 digits (e.g. AB1234567)';
        idNumber.placeholder = '';
        idNumber.minLength = 9;
        idNumber.maxLength = 9;
    } else if (idType === 'national_id') {
        idNumber.pattern = '[0-9]{20}';
        idNumber.title = 'NIDA number must be exactly 20 digits (e.g. 19991234567890000001)';
        idNumber.placeholder = '';
        idNumber.minLength = 20;
        idNumber.maxLength = 20;
    } else {
        idNumber.pattern = '';
        idNumber.title = 'Enter NIDA (20 digits) or Passport (2 letters + 7 digits)';
        idNumber.placeholder = '';
        idNumber.removeAttribute('minlength');
        idNumber.removeAttribute('maxlength');
    }
}

// NIN lookup: fetch user info by NIN and prefill fields
async function lookupNin(nin) {
    try {
        const resp = await fetch('nin_lookup.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'nin=' + encodeURIComponent(nin)
        });
        const data = await resp.json();
        return data;
    } catch (e) {
        return { success: false, message: 'Network error' };
    }
}

document.addEventListener('DOMContentLoaded', function() {
    updateIdNumberField();

    var lookupBtn = document.getElementById('lookup_nin');
    var ninInput = document.getElementById('nin_search');
    lookupBtn && lookupBtn.addEventListener('click', async function() {
        var nin = ninInput.value.trim();
        if (!nin || !/^[0-9]{20}$/.test(nin)) {
            alert('Please enter a valid 20-digit NIN.');
            return;
        }
        lookupBtn.disabled = true;
        lookupBtn.textContent = 'Looking up...';
        const result = await lookupNin(nin);
        lookupBtn.disabled = false;
        lookupBtn.textContent = 'Lookup';
        if (result.success) {
            // Prefill fields
            if (result.data.name) document.getElementById('full_name').value = result.data.name;
            if (result.data.dob) document.getElementById('dob').value = result.data.dob;
            if (result.data.physical_address) document.getElementById('physical_address').value = result.data.physical_address;
            if (result.data.email) document.getElementById('email').value = result.data.email;
            if (result.data.phone) document.getElementById('primary_phone').value = result.data.phone;
            // set ID type and number
            document.getElementById('id_type').value = 'national_id';
            updateIdNumberField();
            document.getElementById('id_number').value = nin;
            // store tin in a hidden field so server-side can validate if needed
            var existing = document.getElementById('user_tin_hidden');
            if (!existing) {
                existing = document.createElement('input');
                existing.type = 'hidden';
                existing.id = 'user_tin_hidden';
                existing.name = 'user_tin_hidden';
                document.querySelector('form.vehicle-form').appendChild(existing);
            }
            existing.value = result.data.tin || '';
            alert('User information populated from NIN. Please complete remaining fields.');
        } else {
            alert(result.message || 'No user found for that NIN.');
        }
    });
});
</script>

        <div class="form-section">
            <h2>Vehicle Information</h2>
            <div class="form-grid">
                <div class="form-group">
                    <label for="vin">Vehicle Identification Number (VIN / Chassis Number) *</label>
                    <input type="text" id="vin" name="vin" required pattern="[A-Za-z0-9]{17}" minlength="17" maxlength="17" title="VIN must be exactly 17 alphanumeric characters" placeholder="e.g. ABC12345678901234" value="<?php echo $edit_mode ? htmlspecialchars($application['vin']) : ''; ?>">
                    <input type="text" id="full_name" name="full_name" required readonly value="<?php echo $edit_mode ? htmlspecialchars($application['full_name']) : (isset($full_name) ? htmlspecialchars($full_name) : ''); ?>">

                <div class="form-group">
                    <label for="engine_number">Engine Number *</label>
                    <input type="text" id="engine_number" name="engine_number" required pattern="[A-Za-z0-9-]{5,30}" title="Enter a valid engine number (5-30 alphanumeric or dash)" placeholder="e.g. ENG123456" value="<?php echo $edit_mode && isset($application['engine_number']) ? htmlspecialchars($application['engine_number']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="make">Vehicle Make *</label>
                    <input type="text" id="make" name="make" required pattern="[A-Za-z0-9\s-]{2,30}" title="Enter a valid make (letters, numbers, spaces, dash)" placeholder="e.g. Toyota" value="<?php echo $edit_mode ? htmlspecialchars($application['make']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="model">Vehicle Model *</label>
                    <input type="text" id="model" name="model" required pattern="[A-Za-z0-9\s-]{1,30}" title="Enter a valid model (letters, numbers, spaces, dash)" placeholder="e.g. Corolla" value="<?php echo $edit_mode ? htmlspecialchars($application['model']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="year">Year of Manufacture *</label>
                    <input type="number" id="year" name="year" min="1900" max="<?php echo date('Y'); ?>" required title="Enter a valid year (1900 to current year)" placeholder="e.g. 2018" value="<?php echo $edit_mode ? htmlspecialchars($application['year']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="vehicle_type">Vehicle Type *</label>
                    <select id="vehicle_type" name="vehicle_type" required>
                        <option value="">Select Type</option>
                        <option value="car" <?php echo ($edit_mode && $application['vehicle_type'] == 'car') ? 'selected' : ''; ?>>Car</option>
                        <option value="motorcycle" <?php echo ($edit_mode && $application['vehicle_type'] == 'motorcycle') ? 'selected' : ''; ?>>Motorcycle</option>
                        <option value="truck" <?php echo ($edit_mode && $application['vehicle_type'] == 'truck') ? 'selected' : ''; ?>>Truck</option>
                        <option value="bus" <?php echo ($edit_mode && $application['vehicle_type'] == 'bus') ? 'selected' : ''; ?>>Bus</option>
                        <option value="other" <?php echo ($edit_mode && $application['vehicle_type'] == 'other') ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="color">Vehicle Color *</label>
                    <select id="color" name="color" required>
                        <option value="" disabled>Select color</option>
                        <option value="Black" <?php if($edit_mode && $application['color']==='Black') echo 'selected'; ?>>Black</option>
                        <option value="White" <?php if($edit_mode && $application['color']==='White') echo 'selected'; ?>>White</option>
                        <option value="Silver" <?php if($edit_mode && $application['color']==='Silver') echo 'selected'; ?>>Silver</option>
                        <option value="Blue" <?php if($edit_mode && $application['color']==='Blue') echo 'selected'; ?>>Blue</option>
                        <option value="Red" <?php if($edit_mode && $application['color']==='Red') echo 'selected'; ?>>Red</option>
                        <option value="Green" <?php if($edit_mode && $application['color']==='Green') echo 'selected'; ?>>Green</option>
                        <option value="Yellow" <?php if($edit_mode && $application['color']==='Yellow') echo 'selected'; ?>>Yellow</option>
                        <option value="Grey" <?php if($edit_mode && $application['color']==='Grey') echo 'selected'; ?>>Grey</option>
                        <option value="Brown" <?php if($edit_mode && $application['color']==='Brown') echo 'selected'; ?>>Brown</option>
                        <option value="Orange" <?php if($edit_mode && $application['color']==='Orange') echo 'selected'; ?>>Orange</option>
                        <option value="Purple" <?php if($edit_mode && $application['color']==='Purple') echo 'selected'; ?>>Purple</option>
                        <option value="Pink" <?php if($edit_mode && $application['color']==='Pink') echo 'selected'; ?>>Pink</option>
                        <option value="Other" <?php if($edit_mode && $application['color']==='Other') echo 'selected'; ?>>Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="fuel_type">Fuel Type *</label>
                    <select id="fuel_type" name="fuel_type" required>
                        <option value="">Select Fuel Type</option>
                        <option value="petrol" <?php echo ($edit_mode && $application['fuel_type'] == 'petrol') ? 'selected' : ''; ?>>Petrol</option>
                        <option value="diesel" <?php echo ($edit_mode && $application['fuel_type'] == 'diesel') ? 'selected' : ''; ?>>Diesel</option>
                        <option value="electric" <?php echo ($edit_mode && $application['fuel_type'] == 'electric') ? 'selected' : ''; ?>>Electric</option>
                        <option value="hybrid" <?php echo ($edit_mode && $application['fuel_type'] == 'hybrid') ? 'selected' : ''; ?>>Hybrid</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="transmission">Transmission *</label>
                    <select id="transmission" name="transmission" required>
                        <option value="">Select Transmission</option>
                        <option value="manual" <?php echo ($edit_mode && isset($application['transmission']) && $application['transmission'] == 'manual') ? 'selected' : ''; ?>>Manual</option>
                        <option value="automatic" <?php echo ($edit_mode && isset($application['transmission']) && $application['transmission'] == 'automatic') ? 'selected' : ''; ?>>Automatic</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="odometer">Odometer Reading *</label>
                    <input type="number" id="odometer" name="odometer" required min="0" max="2000000" title="Enter a valid odometer reading (0-2,000,000)" placeholder="e.g. 50000" value="<?php echo $edit_mode && isset($application['odometer']) ? htmlspecialchars($application['odometer']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="vehicle_photo">Vehicle Photo *</label>
                    <input type="file" id="vehicle_photo" name="vehicle_photo" accept=".jpg,.jpeg,.png" <?php echo $edit_mode ? '' : 'required'; ?>>
                </div>
            </div>
        </div>

        <div class="form-section">
            <h2>Insurance Information</h2>
            <div class="form-grid">
                <div class="form-group">
                    <label for="insurance_provider">Insurance Company Name *</label>
                    <input type="text" id="insurance_provider" name="insurance_provider" required pattern="[A-Za-z0-9\s\.'-]{2,100}" title="Enter a valid company name (letters, numbers, spaces, dot, apostrophe, dash)" placeholder="e.g. Jubilee Insurance" value="<?php echo $edit_mode ? htmlspecialchars($application['insurance_provider']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="policy_number">Insurance Policy Number *</label>
                    <input type="text" id="policy_number" name="policy_number" required pattern="[A-Za-z0-9-]{5,30}" title="Enter a valid policy number (5-30 alphanumeric or dash)" placeholder="e.g. POL123456" value="<?php echo $edit_mode ? htmlspecialchars($application['policy_number']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="insurance_start">Insurance Start Date *</label>
                    <input type="date" id="insurance_start" name="insurance_start" required value="<?php echo $edit_mode && isset($application['insurance_start']) ? htmlspecialchars($application['insurance_start']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="insurance_expiry">Insurance Expiry Date *</label>
                    <input type="date" id="insurance_expiry" name="insurance_expiry" required value="<?php echo $edit_mode && isset($application['insurance_expiry']) ? htmlspecialchars($application['insurance_expiry']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="cover_type">Type of Cover *</label>
                    <select id="cover_type" name="cover_type" required>
                        <option value="">Select Cover Type</option>
                        <option value="third_party" <?php echo ($edit_mode && isset($application['cover_type']) && $application['cover_type'] == 'third_party') ? 'selected' : ''; ?>>Third Party</option>
                        <option value="comprehensive" <?php echo ($edit_mode && isset($application['cover_type']) && $application['cover_type'] == 'comprehensive') ? 'selected' : ''; ?>>Comprehensive</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" name="save_draft" class="btn-secondary" onclick="document.getElementById('form_action').value='save'">
                Save Draft
            </button>
            <button type="submit" name="submit" class="btn-primary" onclick="document.getElementById('form_action').value='submit'">
                Submit Application
            </button>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>