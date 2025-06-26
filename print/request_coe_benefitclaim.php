<?php
require('../fpdf/fpdf.php');
require('../fpdf/html_table.php');
include '../config/connection.php';

$request_id = $_GET['request_id'] ?? '';

$sql = "CALL COE_DATA_BENEFITCLAIM('$request_id')";
$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    die('Error fetching data or no results.');
}

$row = mysqli_fetch_assoc($result);

// === FORMAT VARIABLES ===
$ClaimType = $row['B_ClaimType'];
$coe_type = $row['coe_type'];


if ($coe_type === 'BENEFIT CLAIM WITH COMPENSATION') {
    $raw = $row['B_compensation_details'];
    $raw = str_replace(['&nbsp;', '<div>', '</div>'], [' ', '<br>', ''], $raw);
    $raw = preg_replace('/style="[^"]*"/i', '', $raw);
    $compensation_details = html_entity_decode($raw);
}

$gender     = $row['E_Gender'];
$dateHired  = date("F j, Y", strtotime($row['E_DateHired']));
$position   = strtoupper($row['E_Position']);
$department = $row['E_Area'];

$title_heshe = ($gender == 'FEMALE') ? 'She' : 'He';
$title = ($gender == 'FEMALE') ? 'MS.' : 'MR.';

$EmployeeFirstname = $row['E_Firstname'];
$EmployeeLastname  = $row['E_Lastname'];

mysqli_next_result($conn);

function WriteJustifiedTextWithBold($pdf, $width, $lineHeight, $textParts)
{
    $x = $pdf->GetX();
    $y = $pdf->GetY();

    $buffer = '';
    $styleBuffer = '';
    $line = [];
    $lineWidth = 0;

    foreach ($textParts as $part) {
        $pdf->SetFont('Arial', $part['style'], 12);
        $words = explode(' ', $part['text']);

        foreach ($words as $word) {
            $wordWithSpace = $word . ' ';
            $wordWidth = $pdf->GetStringWidth($wordWithSpace);

            if ($lineWidth + $wordWidth > $width) {
                // Calculate extra space
                $spaceCount = count($line) - 1;
                $remainingWidth = $width - $lineWidth + ($spaceCount * $pdf->GetStringWidth(' '));
                $extraSpace = $spaceCount > 0 ? $remainingWidth / $spaceCount : 0;

                // Print justified line
                foreach ($line as $i => $item) {
                    $pdf->SetFont('Arial', $item['style'], 12);
                    $w = $pdf->GetStringWidth($item['text'] . ' ');
                    $pdf->Cell($w + ($i < $spaceCount ? $extraSpace : 0), $lineHeight, $item['text'], 0, 0, '');
                }
                $pdf->Ln($lineHeight);
                $pdf->SetX($x);
                $line = [];
                $lineWidth = 0;
            }

            $line[] = ['text' => $word, 'style' => $part['style']];
            $lineWidth += $pdf->GetStringWidth($wordWithSpace);
        }
    }

    // Output last line (left-aligned, no justification)
    foreach ($line as $item) {
        $pdf->SetFont('Arial', $item['style'], 12);
        $pdf->Cell($pdf->GetStringWidth($item['text'] . ' '), $lineHeight, $item['text'], 0, 0, '');
    }
    $pdf->Ln($lineHeight);
}



// === GENERATE PDF ===
$pdf = new PDF_HTML();
$pdf->SetMargins(25.4, 25.4, 25.4);
$pdf->AddPage();
$pdf->Ln(40);

// === Title ===
$pdf->SetFont('Arial', 'B', 26);
$pdf->Cell(0, 15, 'CERTIFICATE OF EMPLOYMENT', 0, 1, 'C');
if ($coe_type === 'BENEFIT CLAIM WITH COMPENSATION') {
    $pdf->SetFont('Arial', 'B', 24);
    $pdf->Cell(0, 15, 'WITH COMPENSATION', 0, 1, 'C');
}
$pdf->Ln(10);

$compensation_header = 'and is receiving the following compensation;';

if ($coe_type === 'BENEFIT CLAIM') {
    $compensation_part = 'assigned in ' . $department . ' Department.';
}
if ($coe_type === 'BENEFIT CLAIM WITH COMPENSATION') {
    $compensation_part = 'assigned in ' . $department . ' Department and is receiving the following compensation; ';
}

// === Body Text (Justified) ===
$textParts = [
    ['text' => "    This is to certify that", 'style' => ''],
    ['text' => "$title $EmployeeFirstname $EmployeeLastname", 'style' => 'B'],
    ['text' => "is currently employed with", 'style' => ''],
    ['text' => "PASIG DOCTORS MEDICAL CENTER INC.", 'style' => 'B'],
    ['text' => "located at Pasig City from $dateHired to Present. $title_heshe holds the position as", 'style' => ''],
    ['text' => "$position", 'style' => 'B'],
    ['text' => "$compensation_part", 'style' => '']
];


WriteJustifiedTextWithBold($pdf, 145, 7, $textParts);

$pdf->Ln(5);

if ($coe_type === 'BENEFIT CLAIM WITH COMPENSATION') {
    if (trim($compensation_details) !== '') {
        $pdf->SetFont('Arial', '', 12);
        $pdf->WriteHTML($compensation_details);
        $pdf->Ln(15);
    }
}

// === Purpose ===
$pdf->SetFont('Arial', '', 12);
$pdf->Write(7, '           This certification is being issued upon the request of ');

$pdf->SetFont('Arial', 'B', 12);
$pdf->Write(7, "$title $EmployeeLastname");

$pdf->SetFont('Arial', '', 12);
$pdf->Write(7, ' for ');

$pdf->SetFont('Arial', 'B', 12);
$pdf->Write(7, $ClaimType . ' ');

$pdf->SetFont('Arial', '', 12);
$pdf->Write(7, 'purposes only.');
$pdf->Ln(15);

// === Issued Date ===
$pdf->SetFont('Arial', '', 12);
$pdf->Write(7, '           Issued on the ' . date('jS') . ' day of ' . date('F Y') . '.');

$pdf->Ln(35);

// === Signature ===
$pdf->SetFont('Arial', 'B', 12);
$pdf->Write(7, 'Certified by:');
$pdf->Ln(15);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Write(7, 'MARY GRACE T. PIZARRO');
$pdf->Ln(6);

$pdf->SetFont('Arial', '', 12);
$pdf->Write(7, 'HR Manager');

// === Output PDF ===
$pdf->Output();
