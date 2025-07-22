<?php
include '../../config/connection.php';

$stmt = $conn->prepare("CALL MAINTENANCE_COE_CHECKLIST()");
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
