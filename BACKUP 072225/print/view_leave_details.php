<?php
require('../fpdf/fpdf.php');
include '../config/connection.php';

class PDF extends FPDF
{
    // Page header
    function Header()
    {
        // Logo
        $this->Image('../assets/imgs/pdmc_logo.png', 10, 6, 30);
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(80);
        $this->Cell(110, 10, 'PASIG DOCTORS MEDICAL CENTER', 0, 1, 'C');
        $this->SetFont('Arial', '', 12);
        $this->Cell(80);
        $this->Cell(110, 10, 'Employee Leave Details', 0, 0, 'C');

        $this->SetFont('Arial', '', 8);
        $this->Cell(50, 5, 'Page No. ' . $this->PageNo(), 0, 1, 'R');
        $this->Cell(270, 5, 'Generation Date: ' . date('M d, Y H:i:s'), 0, 1, 'R');
        $this->Ln(10);
    }

    // Page footer
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, '*** GENERATED THRU WTMS ***', 0, 0, 'C');
    }

    // Calculate the number of lines a MultiCell will take
    function NbLines($w, $txt)
    {
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0)
            $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 && $s[$nb - 1] == "\n")
            $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if ($c == ' ')
                $sep = $i;
            $l += $cw[$c];
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j)
                        $i++;
                } else
                    $i = $sep + 1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else
                $i++;
        }
        return $nl;
    }

    // Colored table
    function FancyTable($header, $data)
    {
        // Colors, line width and bold font
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0);
        $this->SetDrawColor(128, 0, 0);
        $this->SetLineWidth(.3);
        $this->SetFont('', 'B', 9);

        // Header
        $w = array(25, 15, 15, 15, 15, 15, 20, 35, 120); // Adjusted widths

        // Main Headers
        $this->Cell($w[0], 7, $header[0][0], 1, 0, 'C', true); // DATE
        $this->Cell($w[1] + $w[2] + $w[3], 7, $header[0][1], 1, 0, 'C', true); // SHIFT
        $this->Cell($w[4] + $w[5] + $w[6] + $w[7] + $w[8], 7, $header[0][2], 1, 0, 'C', true); // LEAVE DETAILS
        $this->Ln();

        // Sub Headers
        for ($i = 0; $i < count($header[1]); $i++)
            $this->Cell($w[$i], 7, $header[1][$i], 1, 0, 'C', true);
        $this->Ln();

        // Data
        $this->SetFont('Arial', '', 8);
        $fill = false;
        foreach ($data as $row) {
            $multicell_text = "Leave ID: " . $row['leaveid'] . "\n" .
                "Endorsement: " . $row['endorsement'] . "\n" .
                "Reason: " . $row['reason'] . "\n" .
                "Approved By: " . $row['taggedby'] . "\n" .
                "Approved Date: " . $row['datetagged'] . " " . $row['timetagged'] . "\n" .
                "Approval Remarks: " . $row['remarks'];

            $rowHeight = $this->NbLines($w[8], $multicell_text) * 4;
            if ($rowHeight < 12) $rowHeight = 12;

            $this->Cell($w[0], $rowHeight, $row['leavedate'], 'LR', 0, 'C', $fill);
            $this->Cell($w[1], $rowHeight, $row['dayname'], 'LR', 0, 'C', $fill);
            $this->Cell($w[2], $rowHeight, substr($row['shiftstart'], 0, 5), 'LR', 0, 'C', $fill);
            $this->Cell($w[3], $rowHeight, substr($row['shiftend'], 0, 5), 'LR', 0, 'C', $fill);
            $this->Cell($w[4], $rowHeight, substr($row['leavetimefrom'], 0, 5), 'LR', 0, 'C', $fill);
            $this->Cell($w[5], $rowHeight, substr($row['leavetimeto'], 0, 5), 'LR', 0, 'C', $fill);
            $this->Cell($w[6], $rowHeight, $row['leaveduration'], 'LR', 0, 'C', $fill);
            $displayLeavetype = $row['leavetype'];
            $maxLen = 20; // Adjust based on your cell width and font size
            if ($this->GetStringWidth($displayLeavetype) > $w[7]) {
                // A more accurate truncation based on width would be better
                $displayLeavetype = substr($displayLeavetype, 0, $maxLen) . '...';
            }
            $this->Cell($w[7], $rowHeight, $displayLeavetype, 'LR', 0, 'C', $fill);

            $x = $this->GetX();
            $y = $this->GetY();
            $this->MultiCell($w[8], 4, $multicell_text, 'LR', 'L', $fill);
            $this->SetXY($x + $w[8], $y);
            $this->Ln($rowHeight);

            $fill = !$fill;
        }
        // Closing line
        $this->Cell(array_sum($w), 0, '', 'T');
    }
}


$employee_id = $_GET['employee_id'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

if (empty($employee_id) || empty($start_date) || empty($end_date)) {
    die('Employee ID, start date, and end date are required.');
}


$stmt = $conn->prepare("CALL COE_PRINT_LEAVE(?, ?, ?)");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("sss", $employee_id, $start_date, $end_date);
if (!$stmt->execute()) {
    die("Execute failed: " . $stmt->error);
}

$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die('No leave details found for the provided criteria.');
}

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}
$stmt->close();
$conn->close();

$pdf = new PDF('L', 'mm', 'A4');
$pdf->SetFont('Arial', '', 10);
$pdf->AddPage();

// Employee Details from the first record
$first_record = $data[0];
$employee_name = $first_record['firstname'] . ' ' . $first_record['middlename'] . ' ' . $first_record['lastname'] . ' ' . $first_record['suffix'];


$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(40, 6, 'DEPARTMENT');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(100, 6, ': ' . $first_record['area']);
$pdf->Ln();
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(40, 6, 'EMPLOYEE ID');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(100, 6, ': ' . $first_record['employeeid']);
$pdf->Ln();
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(40, 6, 'EMPLOYEE NAME');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(100, 6, ': ' . $employee_name);
$pdf->Ln(10);

// Table Header
$header = array(
    array('DATE', 'SHIFT', 'LEAVE DETAILS'),
    array('DATE', 'DAY', 'IN', 'OUT', 'FROM', 'TO', 'DURATION', 'TYPE', 'ENDORSEMENT, REASON, APPROVAL DETAILS')
);

$pdf->FancyTable($header, $data);

$pdf->Output('I', 'Leave_Details.pdf');
