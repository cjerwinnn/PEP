<?php
require_once '../../config/connection.php';
header('Content-Type: application/json');

if (!isset($_GET['employeeid'], $_GET['start'], $_GET['end'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters']);
    exit;
}

$employeeid = $conn2->real_escape_string($_GET['employeeid']);
$startDate = $_GET['start'];
$endDate = $_GET['end'];

$start = new DateTime($startDate);
$end = new DateTime($endDate);
$end->modify('+1 day'); // include end date

$allRows = [];

while ($start < $end) {
    $currentDate = $start->format('Y-m-d');
    $hasRows = false;

    // 1st attempt to fetch DTR data
    $sql = "CALL WEB_DTRView('$employeeid', '$currentDate')";
    if ($result = $conn2->query($sql)) {
        while ($row = $result->fetch_assoc()) {
            $allRows[] = $row;
            $hasRows = true;
        }
        $result->free();
        while ($conn2->more_results()) {
            $conn2->next_result();
        }
    }
    $start->modify('+1 day');
}

echo json_encode($allRows);
$conn2->close();
