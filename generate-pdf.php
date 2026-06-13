<?php
// generate-pdf.php

// Check if session is started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$application_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// This verify the application belongs to the user and is approved
$stmt = $pdo->prepare("SELECT a.*, u.tin, u.email, u.phone 
                      FROM applications a 
                      JOIN users u ON a.user_id = u.user_id 
                      WHERE a.id = ? AND a.user_id = ? AND a.status = 'approved'");
$stmt->execute([$application_id, $user_id]);
$application = $stmt->fetch();

if (!$application) {
    $_SESSION['error'] = "Certificate not available. Application not found, not approved, or you don't have permission to access it.";
    header("Location: dashboard.php");
    exit;
}

// Check if payment is completed
if ($application['payment_status'] !== 'completed') {
    $_SESSION['error'] = "Certificate is only available after payment is completed.";
    header("Location: dashboard.php");
    exit;
}

// Include TCPDF
require_once 'tcpdf/tcpdf.php';

// Create new PDF 
$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('Tanzania Revenue Authority');
$pdf->SetAuthor('TRA Vehicle Registration System');
$pdf->SetTitle('Vehicle Registration Certificate - ' . $application['reference_number']);
$pdf->SetSubject('Official Vehicle Registration Certificate');

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->SetMargins(45, 45, 45);
$pdf->SetAutoPageBreak(TRUE, 20);

$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

$pdf->AddPage();
$pdf->SetDisplayMode('fullwidth', 'SinglePage');

while ($pdf->getNumPages() > 1) {
    $pdf->deletePage($pdf->getNumPages());
}

$pdf->SetFillColor(248, 249, 250);
$pdf->Rect(0, 0, 297, 210, 'F');

$pdf->SetLineWidth(3);
$pdf->SetDrawColor(0, 102, 0); 
$pdf->RoundedRect(15, 15, 267, 180, 8, '1111', 'D');
$pdf->SetLineWidth(2);
$pdf->SetDrawColor(255, 204, 0); 
$pdf->RoundedRect(18, 18, 261, 174, 6, '1111', 'D');
$pdf->SetLineWidth(1);
$pdf->SetDrawColor(0, 51, 153); 
$pdf->RoundedRect(21, 21, 255, 168, 4, '1111', 'D');


$pdf->SetFillColor(0, 51, 153); 
$pdf->SetTextColor(255, 255, 255);
$pdf->RoundedRect(25, 25, 247, 25, 3, '1111', 'F');


$pdf->Image('images/tra_logo.png', 130, 28, 35, 0, 'PNG', '', 'T', false, 300, 'C', false, false, 0, false, false, false);

$pdf->SetY(60);
$pdf->SetFont('helvetica', 'B', 26);
$pdf->SetTextColor(0, 51, 153);
$pdf->Cell(0, 15, 'CERTIFICATE OF VEHICLE REGISTRATION', 0, 1, 'C');

$pdf->SetFont('helvetica', 'I', 14);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(0, 8, 'United Republic of Tanzania', 0, 1, 'C');

$cert_no = 'CERT-' . strtoupper(substr(md5($application_id . $user_id), 0, 12));
$pdf->SetY(85);
$pdf->SetFont('helvetica', '', 11);
$pdf->SetTextColor(127, 140, 141);
$pdf->Cell(0, 6, 'Certificate Number: ' . $cert_no, 0, 1, 'C');

$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColor(0, 51, 153);
$pdf->Cell(0, 8, 'Reference: ' . $application['reference_number'], 0, 1, 'C');
$pdf->SetTextColor(0, 0, 0);

$pdf->Ln(8);


$pdf->SetFont('helvetica', '', 10);
$pdf->SetFillColor(240, 240, 240);

$owner_info = array(
    'Full Name' => $application['full_name'],
    'TIN Number' => $application['tin'],
    'Date of Birth' => (!empty($application['dob']) ? date('F j, Y', strtotime($application['dob'])) : 'N/A'),
    'Email Address' => $application['email'],
    'Contact Phone' => $application['phone'],
    'ID Type' => $application['id_type'],
    'ID Number' => $application['id_number']
);
$vehicle_info = array(
    'Make' => $application['make'],
    'Model' => $application['model'],
    'Year' => $application['year'],
    'Vehicle Type' => $application['vehicle_type'],
    'Color' => $application['color'],
    'Fuel Type' => $application['fuel_type'],
    'VIN/Chassis No.' => $application['vin']
);

$pdf->SetXY(30, 105);
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(115, 10, 'REGISTERED OWNER INFORMATION', 0, 0, 'L', true);
$pdf->Cell(115, 10, 'VEHICLE SPECIFICATIONS', 0, 1, 'L', true);
$pdf->SetFont('helvetica', '', 12);

$maxRows = max(count($owner_info), count($vehicle_info));
for ($i = 0; $i < $maxRows; $i++) {
    $pdf->SetX(30);
    
    if ($i < count($owner_info)) {
        $label = array_keys($owner_info)[$i];
        $value = array_values($owner_info)[$i];
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(50, 10, $label . ':', 0, 0, 'L');
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(65, 10, $value, 0, 0, 'L');
    } else {
        $pdf->Cell(50, 10, '', 0, 0, 'L');
        $pdf->Cell(65, 10, '', 0, 0, 'L');
    }

    if ($i < count($vehicle_info)) {
        $label = array_keys($vehicle_info)[$i];
        $value = array_values($vehicle_info)[$i];
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(50, 10, $label . ':', 0, 0, 'L');
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(65, 10, $value, 0, 0, 'L');
    } else {
        $pdf->Cell(50, 10, '', 0, 0, 'L');
        $pdf->Cell(65, 10, '', 0, 0, 'L');
    }
    $pdf->Ln();
}


$pdf->Ln(8);


$pdf->SetFont('helvetica', 'B', 18);
$pdf->SetTextColor(0, 51, 153);
$pdf->Cell(0, 14, 'REGISTRATION NUMBER', 0, 1, 'C');
$pdf->SetFont('helvetica', 'B', 26);
$pdf->Cell(0, 18, $application['registration_number'], 0, 1, 'C');
$pdf->SetTextColor(0, 0, 0);
$pdf->Ln(8);



// Validity information
$pdf->SetX(30);
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(240, 10, 'Valid From: ' . date('F j, Y') . '    Valid Until: ' . date('F j, Y', strtotime('+1 year')), 0, 1, 'C');

$pdf->SetLineWidth(2);
$pdf->SetDrawColor(200, 0, 0);
$pdf->Circle(80, 150, 20);

$pdf->SetDrawColor(100, 100, 100);
$pdf->Circle(80, 150, 18); 

$pdf->SetDrawColor(200, 0, 0);
$pdf->Circle(80, 150, 16); 

$pdf->SetFont('helvetica', 'B', 7);
$pdf->SetTextColor(100, 100, 100);
$pdf->Text(65, 145, 'TANZANIA REVENUE AUTHORITY');
$pdf->Text(72, 150, 'OFFICIAL SEAL');
$pdf->Text(77, 155, date('Y'));

$pdf->SetLineWidth(0.5);
$pdf->SetDrawColor(0, 0, 0);
$pdf->Line(200, 145, 250, 145);

$pdf->SetFont('helvetica', '', 10);
$pdf->SetXY(200, 147);
$pdf->Cell(50, 6, '_________________________', 0, 1, 'C');
$pdf->SetXY(200, 153);
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(50, 6, 'Director of Transport', 0, 1, 'C');
$pdf->SetXY(200, 159);
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(50, 6, 'Tanzania Revenue Authority', 0, 1, 'C');

$verification_url = 'https://tra.go.tz/verify?cert=' . $cert_no;
$style = array(
    'border' => 0,
    'vpadding' => 'auto',
    'hpadding' => 'auto',
    'fgcolor' => array(0, 51, 153),
    'bgcolor' => false,
    'module_width' => 1,
    'module_height' => 1
);

$pdf->SetFont('helvetica', 'B', 60);
$pdf->SetTextColor(240, 240, 240);
$pdf->SetAlpha(0.05);
$pdf->StartTransform();
$pdf->Rotate(45, 150, 110);
$pdf->Text(40, 100, 'OFFICIAL DOCUMENT');
$pdf->Text(40, 170, 'TANZANIA REVENUE AUTHORITY');
$pdf->StopTransform();
$pdf->SetAlpha(1);
$pdf->SetTextColor(0, 0, 0);


$pdf->SetFont('helvetica', '', 5);
$pdf->SetTextColor(150, 150, 150);
$pdf->SetXY(25, 185);


$pdf->SetFont('helvetica', 'I', 7);
$pdf->SetTextColor(100, 100, 100);
$pdf->SetXY(25, 190);

$pdf->SetFont('helvetica', '', 6);
$pdf->SetXY(25, 200);

while ($pdf->getNumPages() > 2) {
    $pdf->deletePage($pdf->getNumPages());
}
$filename = 'Vehicle_Registration_Certificate_' . $application['reference_number'] . '.pdf';
$pdf->Output($filename, 'D');

exit;
?>