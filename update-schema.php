<?php
include 'config.php';

echo "<h2>Updating Database Schema...</h2>";

try {
    // Drop and recreate valid_nins with new schema
    $pdo->exec("DROP TABLE IF EXISTS valid_nins");
    echo "Dropped existing valid_nins table<br>";
    
    // Create table with new columns
    $sql = "CREATE TABLE IF NOT EXISTS valid_nins (
        nin VARCHAR(20) PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        tin VARCHAR(20) NOT NULL,
        dob DATE,
        physical_address TEXT
    )";
    $pdo->exec($sql);
    echo "Created valid_nins table with dob and physical_address columns<br>";
    
    // Insert test data
    $insertSql = "INSERT INTO valid_nins (nin, name, tin, dob, physical_address) VALUES
    ('11111111111111111111', 'Hamis Monyo', '100000001', '1985-03-15', '123 Main Street, Dar es Salaam'),
    ('22222222222222222222', 'Ahmed Mdabu', '100000002', '1988-07-22', '456 Ocean Road, Dar es Salaam'),
    ('33333333333333333333', 'Nicholas Haule', '100000003', '1992-01-10', '789 Nelson Mandela Road, Arusha'),
    ('44444444444444444444', 'Rose Dario', '100000004', '1990-05-18', '321 Independence Avenue, Dodoma'),
    ('55555555555555555555', 'Mellania Ngomeni', '100000005', '1987-09-25', '654 Kilimanjaro Street, Moshi'),
    ('66666666666666666666', 'Blandina Msume', '100000006', '1993-11-03', '147 Zanzibar Road, Stone Town'),
    ('77777777777777777777', 'Theofil Daniel', '100000007', '1989-06-12', '258 Uhuru Road, Iringa'),
    ('88888888888888888888', 'Joshua Alexander', '100000008', '1986-04-27', '369 Railway Street, Dar es Salaam'),
    ('99999999999999999999', 'Rachel Msigala', '100000009', '1991-08-14', '741 Commercial Street, Mbeya'),
    ('12345678901234567890', 'Robert Dario', '100000010', '1984-02-20', '852 Jamuhuri Street, Dar es Salaam'),
    ('23456789012345678901', 'Ruth David', '100000011', '1988-10-05', '963 King George Street, Dar es Salaam'),
    ('34567890123456789012', 'Anna Modest', '100000012', '1994-12-30', '159 Baobab Avenue, Kigali'),
    ('45678901234567890123', 'Miriam Moses', '100000013', '1989-03-17', '357 Crater Road, Arusha'),
    ('56789012345678901234', 'Rene Peter', '100000014', '1986-07-11', '456 Harbor View, Dar es Salaam'),
    ('67890123456789012345', 'Magreth Anthony', '100000015', '1992-09-23', '789 Lake Shore Drive, Mwanza'),
    ('78901234567890123456', 'Nuru Baraka', '100000016', '1987-05-19', '321 Mountain View, Tanga'),
    ('89012345678901234567', 'Kephlen Idriss', '100000017', '1990-11-08', '654 Valley Road, Kagera'),
    ('90123456789012345678', 'Cesilia Anthony', '100000018', '1988-01-25', '147 Park Street, Dar es Salaam'),
    ('11223344556677889900', 'Tumaini Aron', '100000019', '1991-06-14', '258 Market Road, Moshi'),
    ('22334455667788990011', 'Elisha Elias', '100000020', '1989-04-02', '369 Victoria Street, Dar es Salaam')";
    
    $pdo->exec($insertSql);
    echo "Inserted 20 test records with dob and physical_address<br>";
    
    // Verify
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM valid_nins");
    $result = $stmt->fetch();
    echo "<br><h3 style='color: green;'>Schema update successful!</h3>";
    echo "Total records in valid_nins: " . $result['count'] . "<br>";
    echo "<a href='form.php'>Go to Registration Form</a>";
    
} catch (PDOException $e) {
    echo "<h3 style='color: red;'>Error updating schema: " . $e->getMessage() . "</h3>";
}
?>
