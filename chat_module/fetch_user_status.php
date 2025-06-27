<?php
include '../config/connection.php';
session_start();

header('Content-Type: application/json');

$user_ids_json = $_POST['user_ids'] ?? '[]';
$user_ids = json_decode($user_ids_json, true);

if (empty($user_ids)) {
    echo json_encode([]);
    exit;
}

$placeholders = implode(',', array_fill(0, count($user_ids), '?'));
$types = str_repeat('s', count($user_ids));

// Using $conn2 for hris_live_db
$sql = "SELECT user, last_activity FROM system_users WHERE user IN ($placeholders)";
$stmt = $conn2->prepare($sql);
$stmt->bind_param($types, ...$user_ids);
$stmt->execute();
$result = $stmt->get_result();

$statuses = [];
$active_threshold = 10; // 5 minutes in seconds

date_default_timezone_set('Asia/Manila');

while ($row = $result->fetch_assoc()) {
    $last_activity_timestamp = strtotime($row['last_activity']);
    $current_timestamp = time();
    $is_active = ($current_timestamp - $last_activity_timestamp) < $active_threshold;
    $statuses[$row['user']] = $is_active;
}

echo json_encode($statuses);

$stmt->close();
$conn2->close();
?>