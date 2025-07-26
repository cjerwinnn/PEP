<?php
include '../config/connection.php';

$coe_type = $_POST['coe_type'] ?? '';
$requirements_name = $_POST['requirements_name'] ?? '';
$requirements_description = $_POST['requirements_description'] ?? '';
$checklist_required = isset($_POST['checklist_required']) && $_POST['checklist_required'] == 'true' ? 1 : 0;

// Validate data (add your validation here)

$stmt = $conn->prepare("INSERT INTO request_coe_checklist (coe_type, requirements_name, requirements_description, checklist_required) VALUES (?, ?, ?, ?)");
$stmt->bind_param("sssi", $coe_type, $requirements_name, $requirements_description, $checklist_required);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
