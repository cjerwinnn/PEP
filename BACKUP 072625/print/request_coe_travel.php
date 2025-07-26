<?php
require('../fpdf/fpdf.php');
include '../config/connection.php';

$request_id = $_GET['request_id'] ?? '';

$sql = "CALL COE_DATA_TRAVEL('$request_id')";
$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    die('Error fetching data or no results.');
}

$row = mysqli_fetch_assoc($result);

// === FORMAT VARIABLES ===
$travelFrom     = date("F j, Y", strtotime($row['T_DateFrom']));
$travelTo       = date("F j, Y", strtotime($row['T_DateTo']));
$travelLocation = $row['T_Location'];

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
$pdf = new FPDF();
$pdf->SetMargins(25.4, 25.4, 25.4);
$pdf->AddPage();
$pdf->Ln(40);

// === Title ===
$pdf->SetFont('Arial', 'B', 26);
$pdf->Cell(0, 15, 'CERTIFICATE OF EMPLOYMENT', 0, 1, 'C');
$pdf->Ln(10);

// === Body Text (Justified) ===
$textParts = [
    ['text' => "    This is to certify that", 'style' => ''],
    ['text' => "$title $EmployeeFirstname $EmployeeLastname", 'style' => 'B'],
    ['text' => "is currently employed with", 'style' => ''],
    ['text' => "PASIG DOCTORS MEDICAL CENTER INC.", 'style' => 'B'],
    ['text' => "located at Pasig City from $dateHired to Present. $title_heshe holds the position as", 'style' => ''],
    ['text' => "$position", 'style' => 'B'],
    ['text' => "assigned in $department Department and worked for 40 hours a week.", 'style' => '']
];

WriteJustifiedTextWithBold($pdf, 145, 7, $textParts);

$pdf->Ln(5);

// === Travel Dates ===
$pdf->SetFont('Arial', '', 12);
$pdf->Write(7, "                   Travel Dates: ");

$pdf->SetFont('Arial', 'B', 12);
$pdf->Write(7, "$travelFrom to $travelTo");
$pdf->Ln(8);

// === Travel Destination ===
$pdf->SetFont('Arial', '', 12);
$pdf->Write(7, '                   Travel Destination: ');

$pdf->SetFont('Arial', 'B', 12);
$pdf->Write(7, $travelLocation);
$pdf->Ln(12);

// === Purpose ===
$pdf->SetFont('Arial', '', 12);
$pdf->Write(7, '           This certification is being issued upon the request of ');

$pdf->SetFont('Arial', 'B', 12);
$pdf->Write(7, "$title $EmployeeLastname");

$pdf->SetFont('Arial', '', 12);
$pdf->Write(7, ' for ');

$pdf->SetFont('Arial', 'B', 12);
$pdf->Write(7, 'Travel ');

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
