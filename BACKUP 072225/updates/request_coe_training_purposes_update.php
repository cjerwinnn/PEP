<?php
include '../config/connection.php'; // or adjust path as needed

$request_id = $_POST['request_id'] ?? '';
$emp_title = $_POST['emp_title'] ?? '';
$compensation_details = $_POST['compensation_details'] ?? '';

$stmt = $conn->prepare("UPDATE request_coe_training SET employee_title = ?, purpose_details = ? WHERE request_id = ?");
$stmt->bind_param("sss", $emp_title, $compensation_details, $request_id);

if ($stmt->execute()) {
    echo 'success';
} else {
    echo 'error: ' . $stmt->error;
}

$stmt->close();
