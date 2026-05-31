<?php
include 'config.php';

$password = 'admin123';
$password_hash = password_hash($password, PASSWORD_DEFAULT);

echo "Password: " . $password . "<br>";
echo "Hashed password: " . $password_hash . "<br>";

try {
    $stmt = $pdo->prepare("UPDATE admin_users SET password_hash = ? WHERE username = 'admin'");
    $stmt->execute([$password_hash]);
    
    echo "Admin password updated successfully!<br>";
    echo "You can now login with username: admin, password: admin123<br>";
    echo "<a href='admin/login.php'>Go to Admin Login</a>";
} catch (PDOException $e) {
    echo "Error updating password: " . $e->getMessage();
}
?>
