<?php
include '../../config/connection.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

$s_overtimeid = $_POST['s_overtimeid'] ?? null;

if (!$s_overtimeid) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing s_overtimeid']);
    exit;
}

// Prepare call to stored procedure
$stmt = $conn2->prepare("CALL WEB_OT_DETAILS(?)");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to prepare statement: ' . $conn2->error]);
    exit;
}

$stmt->bind_param("s", $s_overtimeid);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['error' => 'Stored procedure execution failed: ' . $stmt->error]);
    $stmt->close();
    exit;
}

// Get result
$result = $stmt->get_result();
if ($result && $row = $result->fetch_assoc()) {
    echo json_encode($row);
} else {
    echo json_encode(['error' => 'No record found']);
}

// Clean up
$stmt->close();
$conn2->close();
?>
