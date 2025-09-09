<?php
include '../../config/connection.php';

$request_id = $_POST['request_id'] ?? '';
$current_level = isset($_POST['current_level']) ? intval($_POST['current_level']) : 0;

if (!$request_id || $current_level <= 0) {
    http_response_code(response_code: 400);
    echo 'INVALID_INPUT';
    exit;
}

$sql = "SELECT 1
        FROM timekeeping_overtime_approvals_details rd
        JOIN config_approval_flow af
            ON rd.approver_employeeid = af.approvers_employeeid
           AND af.module = 'OT'
        WHERE rd.request_id = ?
          AND rd.approver_level < ?   -- strictly lower levels
          AND (rd.tagged_status != 'APPROVED' OR rd.tagged_status IS NULL)
        LIMIT 1;";

$stmt = $conn2->prepare($sql);
$stmt->bind_param('si', $request_id, $current_level);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    // Found a lower-level approver not yet approved
    echo 'BLOCKED_NOT_FULLY_APPROVED';
} else {
    // All lower levels are already approved
    echo 'PROCEED';
}

$stmt->close();
$conn2->close();
