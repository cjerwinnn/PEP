<?php
include '../config/connection.php';
session_start();
$configBy = isset($_SESSION['employee_id']) ? $_SESSION['employee_id'] : '';

header('Content-Type: application/json');

$module = $_POST['module'] ?? null;
$area = $_POST['area'] ?? null;
$approversJson = $_POST['approvers'] ?? null;

// Basic validation
if (!$module || !$area || !$approversJson) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required parameters.']);
    exit;
}

$approvers = json_decode($approversJson, true);
if (!is_array($approvers) || empty($approvers)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid approvers data.']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO config_approval_flow (module, area, approver_level, approvers_employeeid, override_access, config_by, config_date, config_time) VALUES (?, ?, ?, ?, ?, ?, CURDATE(), CURTIME())");
if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $conn->error]);
    exit;
}

$errors = [];
foreach ($approvers as $approver) {
    $employeeid = $approver['employeeid'] ?? '';
    $level = intval($approver['approver_level'] ?? 0);
    $override = !empty($approver['override_access']) ? 1 : 0;

    if (!$employeeid || $level <= 0) {
        $errors[] = "Invalid data for employee $employeeid.";
        continue;
    }

    $stmt->bind_param('ssisis', $module, $area, $level, $employeeid, $override, $configBy);
    if (!$stmt->execute()) {
        $errors[] = "Failed to insert employee $employeeid: " . $stmt->error;
    }
}

$stmt->close();

if (!empty($errors)) {
    echo json_encode(['status' => 'error', 'message' => implode(' ', $errors)]);
    exit;
}

echo json_encode(['status' => 'success', 'message' => 'Approvers added successfully.']);
