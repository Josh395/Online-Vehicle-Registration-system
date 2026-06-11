<?php
// nin_lookup.php - returns user info for a given NIN (JSON)
include 'config.php';
requireLogin();

header('Content-Type: application/json; charset=utf-8');

$nin = '';
if (isset($_POST['nin'])) {
    $nin = trim($_POST['nin']);
}

if (!$nin || !preg_match('/^[0-9]{20}$/', $nin)) {
    echo json_encode(['success' => false, 'message' => 'Invalid NIN provided']);
    exit;
}

try {
    // First look up the name, tin, dob, and physical_address from valid_nins
    $stmt = $pdo->prepare('SELECT name, tin, dob, physical_address FROM valid_nins WHERE nin = ?');
    $stmt->execute([$nin]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo json_encode(['success' => false, 'message' => 'NIN not found in records']);
        exit;
    }

    $name = $row['name'];
    $tin = $row['tin'];
    $dob = $row['dob'] ?? '';
    $physical_address = $row['physical_address'] ?? '';

    // Try to find a registered user with this TIN to get email/phone
    $stmt = $pdo->prepare('SELECT email, phone FROM users WHERE tin = ? LIMIT 1');
    $stmt->execute([$tin]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $response = [
        'success' => true,
        'data' => [
            'name' => $name,
            'tin' => $tin,
            'dob' => $dob,
            'physical_address' => $physical_address,
            'email' => $user['email'] ?? '',
            'phone' => $user['phone'] ?? ''
        ]
    ];

    echo json_encode($response);
    exit;
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}

?>
