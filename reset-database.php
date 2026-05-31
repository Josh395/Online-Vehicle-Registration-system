
<?php
include 'config.php';

echo "<h2>Resetting Database...</h2>";

try {
    
    $tables = ['uploads', 'payments', 'notifications', 'applications', 'users', 'admin_users', 'registration_history'];
    
    foreach ($tables as $table) {
        try {
            $pdo->exec("DROP TABLE IF EXISTS $table");
            echo "Dropped table: $table<br>";
        } catch (PDOException $e) {
            echo "Error dropping table $table: " . $e->getMessage() . "<br>";
        }
    }

    $sql = "
    CREATE TABLE users (
        user_id INT PRIMARY KEY AUTO_INCREMENT,
        tin VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(20),
        password_hash VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );

    CREATE TABLE admin_users (
        admin_id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        role ENUM('SUPER_ADMIN', 'STAFF') DEFAULT 'STAFF',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE applications (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        reference_number VARCHAR(20) UNIQUE NOT NULL,
        status ENUM('draft', 'submitted', 'under_review', 'approved', 'rejected') DEFAULT 'draft',
        registration_number VARCHAR(20),
        full_name VARCHAR(100),
        dob DATE,
        primary_phone VARCHAR(20),
        email VARCHAR(100),
        physical_address TEXT,
        id_type VARCHAR(50),
        id_number VARCHAR(50),
        vin VARCHAR(17),
        make VARCHAR(50),
        model VARCHAR(50),
        year INT,
        vehicle_type VARCHAR(50),
        color VARCHAR(20),
        fuel_type VARCHAR(20),
        insurance_provider VARCHAR(100),
        policy_number VARCHAR(50),
        total_amount DECIMAL(10,2) DEFAULT 0.00,
        payment_status ENUM('pending', 'completed') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    );

    CREATE TABLE uploads (
        id INT PRIMARY KEY AUTO_INCREMENT,
        application_id INT NOT NULL,
        file_type VARCHAR(50),
        file_path VARCHAR(255) NOT NULL,
        uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE
    );

    CREATE TABLE payments (
        payment_id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        application_id INT NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        payment_method ENUM('bank_transfer', 'mobile_money', 'cash') NOT NULL,
        transaction_id VARCHAR(100),
        status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
        FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE
    );

    CREATE TABLE notifications (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        application_id INT DEFAULT NULL,
        message TEXT NOT NULL,
        is_read TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
        FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE
    );

    CREATE TABLE registration_history (
        history_id INT PRIMARY KEY AUTO_INCREMENT,
        vehicle_id INT,
        action ENUM('SUBMITTED', 'APPROVED', 'REJECTED', 'RENEWED', 'UPDATED') NOT NULL,
        action_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        details TEXT,
        FOREIGN KEY (vehicle_id) REFERENCES applications(id) ON DELETE CASCADE
    );
    ";

    $pdo->exec($sql);
    echo "Tables created successfully!<br>";

    $password = 'admin123';
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO admin_users (username, password_hash, role) VALUES (?, ?, 'SUPER_ADMIN')");
    $stmt->execute(['admin', $password_hash]);
    
    echo "Admin user created successfully!<br>";
    echo "Username: admin<br>";
    echo "Password: admin123<br>";
    echo "Password Hash: " . $password_hash . "<br>";
    
    echo "<h3 style='color: green;'>Database reset successful!</h3>";
    echo "<a href='admin/login.php'>Go to Admin Login</a> | ";
    echo "<a href='index.php'>Go to Homepage</a>";

} catch (PDOException $e) {
    echo "<h3 style='color: red;'>Error resetting database: " . $e->getMessage() . "</h3>";
}
?>
