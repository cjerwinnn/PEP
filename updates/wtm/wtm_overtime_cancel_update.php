<?php
include '../../config/connection.php';

$request_id = $_POST['request_id'] ?? '';
$requestor_EmployeeID = $_POST['requestor_EmployeeID'] ?? '';
$approved_overtime_hours = 0;
$tagged_by = $_POST['tagged_by'] ?? '';
$request_status = $_POST['request_status'] ?? 'CANCELLED'; // default
$approver_level = $_POST['approver_level'] ?? '';
$cancel_remarks = $_POST['cancel_remarks'] ?? '';
$req_status = 'CANCELLED';

$datecreated_mysql = date('Y-m-d');
$timecreated_mysql = date('Y-m-d H:i:s');

try {
    $conn2->begin_transaction();

    $stmt1 = $conn2->prepare("UPDATE timekeeping_overtime_filed SET status = 'CANCELLED' WHERE overtimeid = ?");
    $stmt1->bind_param("s", $request_id);
    $stmt1->execute();

    // LOGS
    $stmt2 = $conn2->prepare("INSERT INTO timekeeping_overtime_logs
            (overtime_id, employee_id, approved_hours, action_remarks, request_status, action_by, action_date, action_time)
            VALUES (?,?,?,?,?,?,?,?)");
    $stmt2->bind_param("ssssssss", $request_id, $requestor_EmployeeID, $approved_overtime_hours, $cancel_remarks, $req_status, $requestor_EmployeeID, $datecreated_mysql, $timecreated_mysql);
    $stmt2->execute();

    $conn2->commit();

    echo "Success";
} catch (Exception $e) {
    $conn2->rollback();
    http_response_code(500);
    echo "Error: " . $e->getMessage();
}
