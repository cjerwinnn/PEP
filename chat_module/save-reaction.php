<?php
include '../config/connection.php';

$id = $_POST['id'] ?? '';
$reaction = $_POST['reaction'] ?? '';

if (!$id || !$reaction) {
    http_response_code(400);
    echo 'INVALID PARAMETERS';
    exit;
}

$stmt = $conn->prepare("UPDATE chat_messages SET reaction = ? WHERE id = ?");
if (!$stmt) {
    http_response_code(500);
    echo 'Prepare failed: ' . $conn->error;
    exit;
}

$stmt->bind_param("si", $reaction, $id);

if (!$stmt->execute()) {
    http_response_code(500);
    echo 'Execute failed: ' . $stmt->error;
    exit;
}

echo 'OK';
?>
