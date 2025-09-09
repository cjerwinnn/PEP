<?php
include '../../config/connection.php';

$requestor_area = $_POST['requestor_area'] ?? '';
$overtime_id = $_POST['overtime_id'] ?? '';

$stmt = $conn2->prepare("CALL WEB_OT_APPROVALFLOW_FETCH(?, ?)");
$stmt->bind_param('ss', $overtime_id, $requestor_area);
$stmt->execute();

$result = $stmt->get_result();
$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

$stmt->close();
$conn2->close();

header('Content-Type: application/json');
echo json_encode($data);
?>
