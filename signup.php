<?php
include 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tin = trim($_POST['tin']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $nin_or_passport = trim($_POST['nin_or_passport']);
    $id_type = trim($_POST['id_type']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if (empty($tin) || empty($name) || empty($email) || empty($password) || empty($nin_or_passport) || empty($id_type)) {
        $error = 'All fields are required';
    } elseif (!preg_match('/^\d{9}$/', $tin)) {
        $error = 'TIN must be exactly 9 digits.';
    } elseif ($id_type === 'national_id' && !preg_match('/^\d{20}$/', $nin_or_passport)) {
        $error = 'NIDA (NIN) must be exactly 20 digits.';
    } elseif ($id_type === 'passport' && !preg_match('/^[A-Za-z]{2}\d{7}$/', $nin_or_passport)) {
        $error = 'Passport must be 2 letters followed by 7 digits.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        try {
            // Check if TIN and name exist in valid_tins table
            $stmt = $pdo->prepare("SELECT tin FROM valid_tins WHERE tin = ? AND name = ?");
            $stmt->execute([$tin, $name]);
            if ($stmt->rowCount() == 0) {
                $error = 'The TIN and Name you entered are not valid for registration.';
            } else {
                // Check NIN/Passport matches name and TIN
                if ($id_type === 'national_id') {
                    $stmt = $pdo->prepare("SELECT nin FROM valid_nins WHERE nin = ? AND name = ? AND tin = ?");
                    $stmt->execute([$nin_or_passport, $name, $tin]);
                    if ($stmt->rowCount() == 0) {
                        $error = 'NIDA (NIN) does not match the provided name and TIN.';
                    }
                } else if ($id_type === 'passport') {
                    $stmt = $pdo->prepare("SELECT passport FROM valid_passports WHERE passport = ? AND name = ? AND tin = ?");
                    $stmt->execute([$nin_or_passport, $name, $tin]);
                    if ($stmt->rowCount() == 0) {
                        $error = 'Passport does not match the provided name and TIN.';
                    }
                }
                // Check if TIN, email, or NIN/Passport exists
                if (!$error) {
                    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE tin = ? OR email = ?");
                    $stmt->execute([$tin, $email]);
                    if ($stmt->rowCount() > 0) {
                        $error = 'TIN or Email already registered';
                    } else {
                        // Hash password and create user
                        $password_hash = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("INSERT INTO users (tin, email, phone, password_hash) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$tin, $email, $phone, $password_hash]);
                        $success = 'Registration successful! You can now login.';
                        header("Refresh: 2; URL=login.php");
                    }
                }
            }
        } catch (PDOException $e) {
            $error = 'Registration failed: ' . $e->getMessage();
        }
    }
}
?>
<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="form-container">
        <h2>Create Account</h2>
        
        <?php if ($error): ?>
            <div class="alert error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="tin">TIN Number *</label>
                <input type="text" id="tin" name="tin" required pattern="\d{9}" title="TIN must be exactly 9 digits" value="<?php echo isset($_POST['tin']) ? htmlspecialchars($_POST['tin']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="name">Full Name *</label>
                <input type="text" id="name" name="name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="id_type">ID Type *</label>
                <select id="id_type" name="id_type" required onchange="updateSignupIdField()">
                    <option value="">Select ID Type</option>
                    <option value="national_id" <?php if(isset($_POST['id_type']) && $_POST['id_type'] == 'national_id') echo 'selected'; ?>>NIDA (NIN)</option>
                    <option value="passport" <?php if(isset($_POST['id_type']) && $_POST['id_type'] == 'passport') echo 'selected'; ?>>Passport</option>
                </select>
            </div>
            <div class="form-group">
                <label for="nin_or_passport">ID Number *</label>
                <input type="text" id="nin_or_passport" name="nin_or_passport" required pattern="[0-9]{20}" minlength="9" maxlength="20" title="" placeholder="" value="<?php echo isset($_POST['nin_or_passport']) ? htmlspecialchars($_POST['nin_or_passport']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="password">Password *</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password *</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn-primary">Register</button>
        </form>
        <script>
        function updateSignupIdField() {
            var idType = document.getElementById('id_type').value;
            var idField = document.getElementById('nin_or_passport');
            if (idType === 'passport') {
                idField.pattern = '[A-Za-z]{2}[0-9]{7}';
                idField.title = 'Passport number must be 2 letters followed by 7 digits (e.g. AB1234567)';
                idField.placeholder = '';
                idField.minLength = 9;
                idField.maxLength = 9;
            } else if (idType === 'national_id') {
                idField.pattern = '[0-9]{20}';
                idField.title = 'NIDA number must be exactly 20 digits (e.g. 19991234567890000001)';
                idField.placeholder = '';
                idField.minLength = 20;
                idField.maxLength = 20;
            } else {
                idField.pattern = '';
                idField.title = 'Enter NIDA (20 digits) or Passport (2 letters + 7 digits)';
                idField.placeholder = '';
                idField.removeAttribute('minlength');
                idField.removeAttribute('maxlength');
            }
        }
        window.onload = updateSignupIdField;
        </script>
        
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>