<?php
// Debug mode
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../config/connection.php';

$coe_type = $_POST['coe_type'] ?? '';

if (!$coe_type) {
    echo json_encode(['error' => 'Missing COE type']);
    exit;
}

// Debug log to file (optional)
file_put_contents('debug.log', "Fetching checklist for COE type: $coe_type\n", FILE_APPEND);

$sql = "SELECT requirements_name, requirements_description, checklist_required 
        FROM request_coe_checklist 
        WHERE coe_type = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $coe_type);
$stmt->execute();
$result = $stmt->get_result();

$requirements = [];
while ($row = $result->fetch_assoc()) {
    $requirements[] = $row;
}

header('Content-Type: application/json');
echo json_encode($requirements);