<?php
include '../config.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['action'])) {
    $id = $_POST['id'];
    $action = $_POST['action'];
    $stmt = $pdo->prepare("SELECT t.user_id, u.email FROM transfer_ownership t JOIN users u ON t.user_id = u.user_id WHERE t.id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    $user_email = $row ? $row['email'] : '';
    if ($action === 'approve') {
        $status = 'approved';
        $stmt = $pdo->prepare("UPDATE transfer_ownership SET status = ?, reviewed_at = NOW(), reviewed_by = ? WHERE id = ?");
        $stmt->execute([$status, $_SESSION['admin_username'], $id]);
        $output = null;
        $retval = null;
        $cmd = escapeshellcmd("php ../generate-transfer-certificate.php id=" . $id);
        exec($cmd, $output, $retval);
        if ($user_email) {
            @mail($user_email, 'Transfer of Ownership Approved', 'Your transfer of ownership request has been approved and completed. Your transfer certificate is now available.');
        }
        $notify = $pdo->prepare("INSERT INTO notifications (user_id, application_id, message, is_read, created_at) VALUES (?, NULL, ?, 0, NOW())");
        $notify->execute([
            $row['user_id'],
            'Your transfer of ownership request has been approved and completed. Your transfer certificate is now available.'
        ]);
    } else {
        $status = 'rejected';
        $reason = isset($_POST['rejection_reason']) ? $_POST['rejection_reason'] : '';
        $stmt = $pdo->prepare("UPDATE transfer_ownership SET status = ?, reviewed_at = NOW(), reviewed_by = ?, rejection_reason = ? WHERE id = ?");
        $stmt->execute([$status, $_SESSION['admin_username'], $reason, $id]);
        if ($user_email) {
            @mail($user_email, 'Transfer of Ownership Rejected', 'Your transfer of ownership request was rejected. Reason: ' . $reason);
        }
        $notify = $pdo->prepare("INSERT INTO notifications (user_id, application_id, message, is_read, created_at) VALUES (?, NULL, ?, 0, NOW())");
        $notify->execute([
            $row['user_id'],
            'Your transfer of ownership request was rejected. Reason: ' . $reason
        ]);
    }
}
header('Location: dashboard.php');
exit;
