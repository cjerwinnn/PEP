<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING); // hide notices/warnings
header('Content-Type: application/json');
include '../../config/connection.php';

$employeeId = $_GET['employeeid'] ?? '';
$shiftDate  = $_GET['shiftdate'] ?? '';

if (empty($employeeId) || empty($shiftDate)) {
    echo json_encode(["total" => 0, "status" => ""]);
    exit;
}

// Prepare statement
$stmt = $conn2->prepare("
    SELECT status
    FROM timekeeping_dtr_manualrequest
    WHERE employeeid = ? AND dtrdate = ?
    ORDER BY daterequested DESC, timerequested DESC
    LIMIT 1
");
$stmt->bind_param("ss", $employeeId, $shiftDate); // both as strings
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

if ($result) {
    echo json_encode([
        "total" => 1,
        "status" => $result['status']
    ]);
} else {
    echo json_encode([
        "total" => 0,
        "status" => ""
    ]);
}

$stmt->close();
$conn2->close();
?>
