<?php
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="icon" type="images" href="../images/tra.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OVRG | ADMIN PORTAL</title>
    <link rel="stylesheet" href="admin-style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;600;700&display=swap" rel="stylesheet">
</head>
<body>
<!-- Admin Navigation -->
<nav class="admin-nav">
    <a href="../index.php"><img src="../images/tra.png"></a>
    <div class="admin-nav-links">
        <ul>
            <li><a href="<?php echo str_replace(' ', '%20', dirname(dirname($_SERVER['PHP_SELF']))) . '/index.php'; ?>">HOME</a></li>
            <li><a href="<?php echo str_replace(' ', '%20', dirname($_SERVER['PHP_SELF'])) . '/dashboard.php'; ?>">DASHBOARD</a></li>
            <li><a href="<?php echo str_replace(' ', '%20', dirname($_SERVER['PHP_SELF'])) . '/logout.php'; ?>">LOGOUT</a></li>
        </ul>
    </div>
</nav>

<!-- Admin Header -->
<div class="admin-header">
    <div class="admin-text-box">
        <h1>Admin Portal</h1>
        <p>Vehicle Registration System Management</p>
    </div>
</div>
