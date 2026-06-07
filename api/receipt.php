<?php
session_start();
require '../config/db.php';
require '../libraries/fpdf.php';

if (!isset($_SESSION['user_id'])) {
    die('Unauthorized');
}

$code = $_GET['code'] ?? '';
if (!$code) die('Booking code tidak ada');

$stmt = $pdo->prepare("
    SELECT b.*, h.name as hotel_name, r.name as room_name
    FROM bookings b
    JOIN hotels h ON b.hotel_id = h.id
    JOIN rooms r  ON b.room_id  = r.id
    WHERE b.booking_code = ? AND b.user_id = ?
");
$stmt->execute([$code, $_SESSION['user_id']]);
$b = $stmt->fetch();

if (!$b) die('Booking tidak ditemukan');

// Hitung malam
$nights = 1;
if ($b['checkin'] && $b['checkout']) {
    $diff   = (new DateTime($b['checkout']))->diff(new DateTime($b['checkin']));
    $nights = max(1, $diff->days);
}
$pricePerNight = $nights > 0 ? round($b['total_price'] / $nights) : $b['total_price'];

// Warna palette Tripzy
// Hot Pink     #E05297 → R:224 G:82  B:151
// Warm Yellow  #FFD372 → R:255 G:211 B:114
// Charcoal     #2B2B2B → R:43  G:43  B:43
// Blush        #FFB1D4 → R:255 G:177 B:212

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(true, 15);

// ── HEADER: Nama brand ──
$pdf->SetFont('Arial', 'B', 28);
$pdf->SetTextColor(43, 43, 43);
$pdf->Cell(0, 14, 'Tripzy', 0, 1, 'C');

$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(150, 150, 150);
$pdf->Cell(0, 5, 'tripzy.com  |  hello@tripzy.com  |  +62 800-TRIPZY', 0, 1, 'C');
$pdf->Ln(3);

// Garis tipis
$pdf->SetDrawColor(255, 177, 212); // Blush
$pdf->SetLineWidth(0.4);
$pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
$pdf->Ln(6);

// ── BARIS: Paid By | RECEIPT ──
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(224, 82, 151); // Hot Pink
$pdf->Cell(100, 6, 'Paid By', 0, 0);

$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor(43, 43, 43);
$pdf->Cell(80, 6, 'RECEIPT', 0, 1, 'R');

$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(43, 43, 43);
$pdf->Cell(100, 6, $b['guest_name'], 0, 0);
$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(150, 150, 150);
$pdf->Cell(80, 6, 'Phone: ' . $b['guest_phone'], 0, 1, 'R');

$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(150, 150, 150);
$pdf->Cell(100, 5, 'KTP: ' . $b['guest_ktp'], 0, 1);
$pdf->Ln(5);

// ── BOOKING DETAILS ──
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(224, 82, 151);
$pdf->Cell(0, 7, 'Booking Details', 0, 1);

$pdf->SetDrawColor(200, 200, 200);
$pdf->SetLineWidth(0.2);
$pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
$pdf->Ln(3);

$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(43, 43, 43);

// Left column details
$leftW  = 45;
$rightW = 90;

$details = [
    ['Check-in',        $b['checkin']  ?? '-'],
    ['Check-out',       $b['checkout'] ?? '-'],
    ['Guests',          $b['adult_count'] . ' Adults, ' . $b['child_count'] . ' Children'],
    ['Room',            $b['room_name']],
    ['Hotel',           $b['hotel_name']],
    ['Payment',         $b['payment_method']],
];

// Right side info
$rightInfo = [
    'Receipt #'    => $b['booking_code'],
    'Receipt Date' => date('d-m-Y'),
    'Status'       => strtoupper($b['status']),
];

$yStart = $pdf->GetY();
foreach ($details as $i => $row) {
    $pdf->SetFont('Arial', '', 10);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell($leftW, 7, $row[0], 0, 0);
    $pdf->SetTextColor(43, 43, 43);
    $pdf->Cell($rightW, 7, $row[1], 0, 1);
}

// Right info block (overlaid)
$pdf->SetXY(130, $yStart);
$keys = array_keys($rightInfo);
foreach ($keys as $key) {
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetTextColor(224, 82, 151);
    $pdf->Cell(35, 7, $key, 0, 0);
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(43, 43, 43);
    $pdf->Cell(30, 7, $rightInfo[$key], 0, 1, 'R');
    $pdf->SetX(130);
}

$pdf->Ln(4);

// ── TABEL ITEM ──
// Header tabel
$pdf->SetFillColor(43, 43, 43); // Charcoal
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(20, 8, 'Qty', 1, 0, 'C', true);
$pdf->Cell(80, 8, 'Description', 1, 0, 'L', true);
$pdf->Cell(40, 8, 'Unit Price', 1, 0, 'R', true);
$pdf->Cell(40, 8, 'Amount', 1, 1, 'R', true);

// Row: Nights
$pdf->SetFillColor(255, 245, 250); // light blush bg
$pdf->SetTextColor(43, 43, 43);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(20, 7, $nights, 1, 0, 'C', true);
$pdf->Cell(80, 7, 'Nights at ' . $b['hotel_name'], 1, 0, 'L', true);
$pdf->Cell(40, 7, 'Rp ' . number_format($pricePerNight, 0, ',', '.'), 1, 0, 'R', true);
$pdf->Cell(40, 7, 'Rp ' . number_format($b['total_price'], 0, ',', '.'), 1, 1, 'R', true);

// Row: Room type
$pdf->Cell(20, 7, $b['room_count'], 1, 0, 'C', true);
$pdf->Cell(80, 7, $b['room_name'], 1, 0, 'L', true);
$pdf->Cell(40, 7, '-', 1, 0, 'R', true);
$pdf->Cell(40, 7, '-', 1, 1, 'R', true);

// Total row
$pdf->SetFillColor(255, 211, 114); // Warm Yellow
$pdf->SetTextColor(43, 43, 43);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(140, 8, 'Total', 1, 0, 'R', true);
$pdf->Cell(40, 8, 'Rp ' . number_format($b['total_price'], 0, ',', '.'), 1, 1, 'R', true);

$pdf->Ln(6);

// ── NOTES ──
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(224, 82, 151);
$pdf->Cell(0, 6, 'Notes', 0, 1);
$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(100, 100, 100);
$pdf->MultiCell(0, 5, 'Thank you for choosing Tripzy. We hope you have a wonderful stay! For inquiries, contact hello@tripzy.com');

$pdf->Ln(4);
$pdf->SetFont('Arial', 'I', 8);
$pdf->SetTextColor(180, 180, 180);
$pdf->Cell(0, 5, 'Generated on ' . date('d M Y, H:i') . ' | tripzy.com', 0, 1, 'C');

$pdf->Output('D', 'Tripzy-Receipt-' . $b['booking_code'] . '.pdf');