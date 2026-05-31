<?php
include 'config.php';

if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tin = trim($_POST['tin']);
    $password = $_POST['password'];
    
    if (empty($tin) || empty($password)) {
        $error = 'Please enter TIN and password';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT u.user_id, u.tin, u.password_hash, v.name FROM users u JOIN valid_tins v ON u.tin = v.tin WHERE u.tin = ?");
            $stmt->execute([$tin]);
            $user = $stmt->fetch();

            if (!$user) {
                $error = 'User does not exist';
            } elseif (password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_name'] = $user['name'];
                header("Location: dashboard.php");
                exit;
            } else {
                $error = 'Invalid TIN or password';
            }
        } catch (PDOException $e) {
            $error = 'Login failed: ' . $e->getMessage();
        }
    }
}
?>
<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="form-container">
        <h2>User Login</h2>
        
        <?php if ($error): ?>
            <div class="alert error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="tin">TIN Number</label>
                <input type="text" id="tin" name="tin" required value="<?php echo isset($_POST['tin']) ? htmlspecialchars($_POST['tin']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn-primary">Login</button>
        </form>
        
        <p>Don't have an account? <a href="signup.php">Register here</a></p>
        
    </div>
</div>

<?php include 'includes/footer.php'; ?>