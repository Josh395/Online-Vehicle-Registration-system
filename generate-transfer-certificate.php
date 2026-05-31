<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';
require_once 'tcpdf/tcpdf.php';

if (!isset($_GET['id'])) {
    die('Missing transfer ID.');
}

$transfer_id = $_GET['id'];

$stmt = $pdo->prepare("SELECT t.*, u.email, u.tin, u.phone FROM transfer_ownership t JOIN users u ON t.user_id = u.user_id WHERE t.id = ?");
$stmt->execute([$transfer_id]);
$transfer = $stmt->fetch();

if (!$transfer || $transfer['status'] !== 'approved') {
    die('Transfer not found or not approved.');
}

// Create PDF
$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('Tanzania Revenue Authority');
$pdf->SetAuthor('TRA Vehicle Registration System');
$pdf->SetTitle('Transfer of Ownership Certificate');
$pdf->SetSubject('Official Transfer of Ownership Certificate');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(45, 45, 45);
$pdf->SetAutoPageBreak(TRUE, 20);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->AddPage();
$pdf->SetDisplayMode('fullwidth', 'SinglePage');
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

if (file_exists('images/tra_logo.png')) {
    $pdf->Image('images/tra_logo.png', 130, 28, 35, 0, 'PNG', '', 'T', false, 300, 'C', false, false, 0, false, false, false);
}
$pdf->SetY(60);
$pdf->SetFont('helvetica', 'B', 24);
$pdf->SetTextColor(0, 51, 153);
$pdf->Cell(0, 15, 'CERTIFICATE OF TRANSFER OF OWNERSHIP', 0, 1, 'C');
$pdf->SetFont('helvetica', 'I', 14);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(0, 8, 'United Republic of Tanzania', 0, 1, 'C');
$cert_no = 'TRANS-' . strtoupper(substr(md5($transfer_id . $transfer['user_id']), 0, 12));
$pdf->SetY(85);
$pdf->SetFont('helvetica', '', 11);
$pdf->SetTextColor(127, 140, 141);
$pdf->Cell(0, 6, 'Certificate Number: ' . $cert_no, 0, 1, 'C');
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColor(0, 51, 153);
$pdf->Cell(0, 8, 'Reference: ' . ($transfer['vehicle_reg_number'] ?? 'N/A'), 0, 1, 'C');
$pdf->SetTextColor(0, 0, 0);
$pdf->Ln(8);
$pdf->SetFont('helvetica', '', 10);
$pdf->SetFillColor(240, 240, 240);
$pdf->SetXY(30, 105);
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(115, 10, 'NEW OWNER INFORMATION', 0, 0, 'L', true);
$pdf->Cell(115, 10, 'VEHICLE DETAILS', 0, 1, 'L', true);
$pdf->SetFont('helvetica', '', 12);
$owner_info = array(
    'Full Name' => $transfer['buyer_full_name'] ?? 'N/A',
    'TIN Number' => $transfer['tin'] ?? 'N/A',
    'Email Address' => $transfer['buyer_email'] ?? $transfer['email'] ?? 'N/A',
    'Contact Phone' => $transfer['buyer_phone'] ?? $transfer['phone'] ?? 'N/A'
);
$vehicle_info = array(
    'Registration Number' => $transfer['vehicle_reg_number'] ?? 'N/A',
    'Make' => $transfer['make'] ?? 'N/A',
    'Model' => $transfer['model'] ?? 'N/A',
    'Year' => $transfer['year_manufacture'] ?? 'N/A',
    'Vehicle Type' => $transfer['vehicle_type'] ?? 'N/A',
    'Color' => $transfer['color'] ?? 'N/A',
    'VIN/Chassis No.' => $transfer['vehicle_vin'] ?? 'N/A'
);
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
$pdf->Cell(0, 14, 'TRANSFER DATE', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 14);
$pdf->Cell(0, 10, date('F j, Y', strtotime($transfer['reviewed_at'])), 0, 1, 'C');
$pdf->SetTextColor(0, 0, 0);
$pdf->Ln(8);
$pdf->SetFont('helvetica', '', 10);
$pdf->SetXY(200, 147);
$pdf->Cell(50, 6, '_________________________', 0, 1, 'C');
$pdf->SetXY(200, 153);
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(50, 6, 'Director of Transport', 0, 1, 'C');
$pdf->SetXY(200, 159);
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(50, 6, 'Tanzania Revenue Authority', 0, 1, 'C');
$pdf->SetFont('helvetica', 'I', 7);
$pdf->SetTextColor(100, 100, 100);
$pdf->SetXY(25, 190);
$pdf->Cell(0, 6, 'This certificate is issued as proof of transfer of vehicle ownership.', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 6);
$pdf->SetXY(25, 200);
$pdf->Cell(0, 6, 'Issued on: ' . date('F j, Y, H:i'), 0, 1, 'C');

$filename = 'Transfer_Certificate_' . ($transfer['vehicle_reg_number'] ?? '') . '_' . $cert_no . '.pdf';
$pdf->Output($filename, 'D');
exit;
