<?php
include '../config.php';
requireAdmin();

if (!isset($_POST['id']) && !isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$application_id = isset($_POST['id']) ? $_POST['id'] : $_GET['id'];
$rejection_reason = $_POST['rejection_reason'] ?? '';

// Get application details
$stmt = $pdo->prepare("SELECT a.*, u.tin, u.email FROM applications a JOIN users u ON a.user_id = u.user_id WHERE a.id = ?");
$stmt->execute([$application_id]);
$application = $stmt->fetch();

if (!$application) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (empty($rejection_reason)) {
        $_SESSION['error'] = "Please provide a reason for rejection";
        header("Location: view-application.php?id=$application_id");
        exit;
    }
    
    try {
        // Update application status
        $stmt = $pdo->prepare("UPDATE applications SET status = 'rejected' WHERE id = ?");
        $stmt->execute([$application_id]);
        
        // Create notification for user
        $message = "Your vehicle registration application " . $application['reference_number'] . " has been rejected. Reason: $rejection_reason";
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, application_id, message) VALUES (?, ?, ?)");
        $stmt->execute([$application['user_id'], $application_id, $message]);
        
        $_SESSION['success'] = "Application rejected successfully";
        header("Location: view-application.php?id=$application_id");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error rejecting application: " . $e->getMessage();
        header("Location: view-application.php?id=$application_id");
        exit;
    }
} else {
    header("Location: view-application.php?id=$application_id");
    exit;
}
?>