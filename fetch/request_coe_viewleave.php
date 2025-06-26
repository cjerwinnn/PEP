<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

include '../config/connection.php';  // your DB connection file

$employee_id = $_GET['employee_id'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

if (!$employee_id || !$start_date || !$end_date) {
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

// Prepare and execute stored procedure
$sql = "CALL COE_VIEW_LEAVE(?, ?, ?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param('sss', $employee_id, $start_date, $end_date);

if (!$stmt->execute()) {
    echo json_encode(['error' => 'Execute failed: ' . $stmt->error]);
    exit;
}

// Stored procedure results
$result = $stmt->get_result();

if (!$result) {
    echo json_encode(['error' => 'Getting result failed: ' . $stmt->error]);
    exit;
}

$leaves = [];

while ($row = $result->fetch_assoc()) {
    $leaves[] = $row;
}

echo json_encode($leaves);
?>
