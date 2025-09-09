<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

include '../../config/connection.php';

$employees = [];

$department = $_GET['department'] ?? '';
$area = $_GET['area'] ?? '';
$month = $_GET['month'] ?? date('n');
$year = $_GET['year'] ?? date('Y');

$stmt = $conn2->prepare("CALL WEB_OT_APPROVAL_LIST(?, ?, ?, ?)");
if (!$stmt) {
    echo json_encode(['error' => 'Prepare failed: ' . $conn2->error]);
    exit;
}

$stmt->bind_param('ssii', $department, $area, $month, $year);

if (!$stmt->execute()) {
    echo json_encode(['error' => 'Execute failed: ' . $stmt->error]);
    exit;
}

// Attempt to get results
$result = $stmt->get_result();
if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Convert BLOB to base64 if exists
        if (isset($row['picture']) && $row['picture']) {
            $row['image'] = 'data:image/png;base64,' . base64_encode($row['picture']);
        } else {
            $row['image'] = '../assets/imgs/user_default.png';
        }
        // Remove the original BLOB to reduce JSON size
        unset($row['picture']);
        $employees[] = $row;
    }
} else {
    // Fallback for drivers without get_result()
    $stmt->store_result();
    $meta = $stmt->result_metadata();
    if ($meta) {
        $fields = [];
        $row = [];
        while ($field = $meta->fetch_field()) {
            $fields[] = &$row[$field->name];
        }
        call_user_func_array([$stmt, 'bind_result'], $fields);
        while ($stmt->fetch()) {
            // Convert picture BLOB to base64
            if (isset($row['picture']) && $row['picture']) {
                $row['image'] = 'data:image/png;base64,' . base64_encode($row['picture']);
            } else {
                $row['image'] = '../assets/imgs/user_default.png';
            }
            unset($row['picture']);
            $employees[] = array_map(fn($v) => $v, $row);
        }
    }
}

$stmt->close();
$conn2->next_result();
$conn2->close();

echo json_encode($employees);
exit;
