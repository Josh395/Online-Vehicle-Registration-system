<?php
include '../config.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $application_id = $_POST['id'];
    $plate_number = trim($_POST['plate_number']);
    $pickup_date = trim($_POST['pickup_date']);
    if (empty($plate_number) || empty($pickup_date)) {
        header('Location: view-application.php?id=' . $application_id . '&error=Missing+fields');
        exit;
    }
    // Assign plate number and pickup date
    $stmt = $pdo->prepare("UPDATE applications SET registration_number = ?, plate_pickup_date = ? WHERE id = ?");
    $stmt->execute([$plate_number, $pickup_date, $application_id]);
    // Get user_id for notification
    $stmt = $pdo->prepare("SELECT user_id FROM applications WHERE id = ?");
    $stmt->execute([$application_id]);
    $row = $stmt->fetch();
    if ($row) {
        $message = "Your plate number ($plate_number) is ready. Please pick it up on $pickup_date.";
        $notifyStmt = $pdo->prepare("INSERT INTO notifications (user_id, application_id, message) VALUES (?, ?, ?)");
        $notifyStmt->execute([$row['user_id'], $application_id, $message]);
    }
    header('Location: view-application.php?id=' . $application_id . '&success=Plate+assigned');
    exit;
}
?>
