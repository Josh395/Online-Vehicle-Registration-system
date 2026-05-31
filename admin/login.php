<?php
include '../config.php';

// Redirect if already logged in
if (isAdminLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    if (empty($username) || empty($password)) {
        $error = 'Please enter username and password';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT admin_id, username, password_hash, role FROM admin_users WHERE username = ? LIMIT 1");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password_hash'])) {
                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_role'] = $admin['role'];
                header("Location: dashboard.php");
                exit;
            } else {
                $error = 'Invalid username or password';
            }
        } catch (PDOException $e) {
            $error = 'Login failed: ' . $e->getMessage();
        }
    }
}
?>
<?php include 'includes/header.php'; ?>

<div class="login-container">
    <div class="login-form">
        <div class="login-header">
            <img src="../images/tra.png" alt="TRA Logo" height="60">
            <h2>Admin Login</h2>
        </div>
        
        <?php if ($error): ?>
            <div class="alert error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" autocomplete="off">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autocomplete="off" value="">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="off" value="">
            </div>
            <button type="submit" class="admin-btn btn-primary">Login</button>
        </form>
        
        <div class="login-footer">
            <a href="../index.php">← Back to Home</a>
            <br>
            <a href="../setup-admin-password.php" style="color: #666; font-size: 0.9rem;">
                Having login issues? Reset admin password
            </a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
