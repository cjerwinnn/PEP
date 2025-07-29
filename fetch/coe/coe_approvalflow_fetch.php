<?php
include '../../config/connection.php';

$requestor_area = $_POST['requestor_area'] ?? '';
$request_id = $_POST['request_id'] ?? '';

$stmt = $conn->prepare("CALL COE_APPROVALFLOW_FETCH(?, ?)");
$stmt->bind_param('ss', $request_id, $requestor_area);
$stmt->execute();

$result = $stmt->get_result();
$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($data);
?>
