<?php
include '../config/connection.php';

$status_counts = [
    'requested' => 0,
    'approved' => 0,
    'onprocess' => 0,
    'onhold' => 0,
    'forsigning' => 0,
    'forreleasing' => 0,
    'released' => 0,
    'denied' => 0
];

$stmt = $conn->prepare("CALL DASHBOARD_COE_COUNT()");
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    foreach ($status_counts as $key => $val) {
        $status_counts[$key] = (int) ($row[$key] ?? 0);
    }
}

$stmt->close();
$conn->close();

// Output spans with class hooks
foreach ($status_counts as $status => $count) {
    echo "<span class='refresh-count' data-status='$status'>$count</span>";
}
