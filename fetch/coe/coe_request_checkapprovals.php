<?php
include '../../config/connection.php';

$request_id = $_POST['request_id'] ?? '';
$current_level = isset($_POST['current_level']) ? intval($_POST['current_level']) : 0;

if (!$request_id || $current_level <= 0) {
    http_response_code(response_code: 400);
    echo 'INVALID_INPUT';
    exit;
}

$sql = "SELECT af.approver_level
        FROM config_approval_flow af
        LEFT JOIN request_coe_approvals_details rd ON af.approver_level = rd.approver_level 
          AND rd.request_id = ?
        WHERE af.module = 'COE'
          AND af.approver_level < ?
          AND (rd.tagged_status != 'APPROVED' OR rd.tagged_status IS NULL)";

$stmt = $conn->prepare($sql);
$stmt->bind_param('si', $request_id, $current_level);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    // There is at least one lower level approver who has NOT approved yet
    echo 'BLOCKED_NOT_FULLY_APPROVED';
} else {
    // All lower levels have approved
    echo 'PROCEED';
}

$stmt->close();
$conn->close();
?>
