<?php
include '../config.php';
requireAdmin();

if (!isset($_POST['id']) && !isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$application_id = isset($_POST['id']) ? $_POST['id'] : $_GET['id'];

$stmt = $pdo->prepare("SELECT a.*, u.tin, u.email FROM applications a JOIN users u ON a.user_id = u.user_id WHERE a.id = ?");
$stmt->execute([$application_id]);
$application = $stmt->fetch();

if (!$application) {
    header("Location: dashboard.php");
    exit;
}

// Generate plate number (format: T 123 ABC)
function generatePlateNumber() {
    $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $numbers = '0123456789';
    
    $plate = 'T ';
    for ($i = 0; $i < 3; $i++) {
        $plate .= $numbers[rand(0, 9)];
    }
    $plate .= ' ';
    for ($i = 0; $i < 3; $i++) {
        $plate .= $letters[rand(0, 25)];
    }
    
    return $plate;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Only assign a new plate number and pickup message
        if (empty($application['registration_number'])) {
            $plate_number = generatePlateNumber();
            $stmt = $pdo->prepare("UPDATE applications SET status = 'approved', registration_number = ? WHERE id = ?");
            $stmt->execute([$plate_number, $application_id]);

            // Create notification for user: approval
            $message = "Your vehicle registration application " . $application['reference_number'] . " has been approved. Plate number: $plate_number. Your application is now approved.";
            $stmt = $pdo->prepare("INSERT INTO notifications (user_id, application_id, message) VALUES (?, ?, ?)");
            $stmt->execute([$application['user_id'], $application_id, $message]);

            // Plate pickup message
            $today = date('Y-m-d');
            $plate_ready_date = date('Y-m-d', strtotime($today. ' + 5 days'));
            $pickup_location = $application['physical_address'];
            $pickup_office = '';
            if (stripos($pickup_location, 'Dodoma') !== false) {
                $pickup_office = 'TRA Dodoma Regional Office';
            } elseif (stripos($pickup_location, 'Dar es Salaam') !== false) {
                $pickup_office = 'TRA Dar es Salaam Regional Office';
            } elseif (stripos($pickup_location, 'Arusha') !== false) {
                $pickup_office = 'TRA Arusha Regional Office';
            } elseif (stripos($pickup_location, 'Mwanza') !== false) {
                $pickup_office = 'TRA Mwanza Regional Office';
            } elseif (stripos($pickup_location, 'Mbeya') !== false) {
                $pickup_office = 'TRA Mbeya Regional Office';
            } elseif (stripos($pickup_location, 'Tanga') !== false) {
                $pickup_office = 'TRA Tanga Regional Office';
            } elseif (stripos($pickup_location, 'Morogoro') !== false) {
                $pickup_office = 'TRA Morogoro Regional Office';
            } elseif (stripos($pickup_location, 'Kilimanjaro') !== false) {
                $pickup_office = 'TRA Kilimanjaro Regional Office';
            } elseif (stripos($pickup_location, 'Tabora') !== false) {
                $pickup_office = 'TRA Tabora Regional Office';
            } elseif (stripos($pickup_location, 'Mtwara') !== false) {
                $pickup_office = 'TRA Mtwara Regional Office';
            } elseif (stripos($pickup_location, 'Singida') !== false) {
                $pickup_office = 'TRA Singida Regional Office';
            } elseif (stripos($pickup_location, 'Shinyanga') !== false) {
                $pickup_office = 'TRA Shinyanga Regional Office';
            } elseif (stripos($pickup_location, 'Iringa') !== false) {
                $pickup_office = 'TRA Iringa Regional Office';
            } elseif (stripos($pickup_location, 'Ruvuma') !== false) {
                $pickup_office = 'TRA Ruvuma Regional Office';
            } elseif (stripos($pickup_location, 'Pwani') !== false) {
                $pickup_office = 'TRA Pwani Regional Office';
            } elseif (stripos($pickup_location, 'Lindi') !== false) {
                $pickup_office = 'TRA Lindi Regional Office';
            } elseif (stripos($pickup_location, 'Manyara') !== false) {
                $pickup_office = 'TRA Manyara Regional Office';
            } elseif (stripos($pickup_location, 'Geita') !== false) {
                $pickup_office = 'TRA Geita Regional Office';
            } elseif (stripos($pickup_location, 'Simiyu') !== false) {
                $pickup_office = 'TRA Simiyu Regional Office';
            } elseif (stripos($pickup_location, 'Njombe') !== false) {
                $pickup_office = 'TRA Njombe Regional Office';
            } else {
                $pickup_office = 'your local TRA office';
            }
            $pickup_message = "Your plates are ready. Pick them at $pickup_office on $plate_ready_date.";
            $stmt = $pdo->prepare("INSERT INTO notifications (user_id, application_id, message) VALUES (?, ?, ?)");
            $stmt->execute([$application['user_id'], $application_id, $pickup_message]);
        } else {
            $stmt = $pdo->prepare("UPDATE applications SET status = 'approved' WHERE id = ?");
            $stmt->execute([$application_id]);
            $plate_number = $application['registration_number'];
            $message = "Your vehicle registration renewal application " . $application['reference_number'] . " has been approved. Plate number: $plate_number. Your renewal is now approved.";
            $stmt = $pdo->prepare("INSERT INTO notifications (user_id, application_id, message) VALUES (?, ?, ?)");
            $stmt->execute([$application['user_id'], $application_id, $message]);
        }

        $_SESSION['success'] = "Application approved successfully. Plate number: $plate_number";
        header("Location: view-application.php?id=$application_id");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error approving application: " . $e->getMessage();
        header("Location: view-application.php?id=$application_id");
        exit;
    }
} else {
    header("Location: view-application.php?id=$application_id");
    exit;
}
?>