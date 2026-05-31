<?php
// includes/header.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Vehicle Registration System</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <img src="images/tra.png" alt="TRA Logo" height="50">
                <span>Vehicle Registration</span>
            </div>
            <ul class="nav-menu">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="form.php">New Application</a></li>
                    <li><a href="renewal.php">Renew Application</a></li>
                    <li><a href="transfer-ownership.php">Transfer Ownership</a></li>
                    <li><a href="logout.php">Logout</a></li>
                    
                <?php elseif (isset($_SESSION['admin_id'])): ?>
                    <li><a href="admin/dashboard.php">Admin Dashboard</a></li>
                    <li><a href="admin/logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="signup.php">Register</a></li>
                    <li><a href="admin/login.php">Admin</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <main>