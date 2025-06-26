<?php
include '../config/connection.php'; // or adjust path as needed

$request_id = $_POST['request_id'] ?? '';
$compensation_details = $_POST['compensation_details'] ?? '';

$stmt = $conn->prepare("UPDATE request_coe_financial SET purpose_details = ? WHERE request_id = ?");
$stmt->bind_param("ss", $compensation_details, $request_id);

if ($stmt->execute()) {
    echo 'success';
} else {
    echo 'error: ' . $stmt->error;
}

$stmt->close();
