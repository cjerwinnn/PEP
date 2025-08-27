<?php
header('Content-Type: application/json');

include '../../config/connection.php';

$area = $_GET['area'];

$stmt = $conn->prepare("CALL MAINTENANCE_COE_APPROVALFLOW_EMPLOYEES(?)");
$stmt->bind_param('s', $area);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => $conn->error]);
    exit;
}

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['error' => $stmt->error]);
    exit;
}

$result = $stmt->get_result();
if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => $stmt->error]);
    exit;
}

$employees = [];
while ($row = $result->fetch_assoc()) {
    $employees[] = $row;
}

echo json_encode($employees);

$stmt->close();
$conn->close();
