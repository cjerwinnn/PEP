<?php
include '../config/connection.php';

$request_id = $_POST['request_id'];
$employee_id = $_POST['employee_id'];
$req_coe_type = $_POST['req_coe_type'];

// Header data
$request_reason = $_POST['request_reason'];
$date_needed = $_POST['date_needed'];
$request_format = $_POST['request_format'];
$requested_date = $_POST['requested_date'];
$requested_time = $_POST['requested_time'];
$requested_by = $_POST['requested_by'];
$tagged_by = $_POST['tagged_by'];
$request_status = $_POST['request_status'];

$onprocess_remarks = $_POST['onprocess_remarks'];
$req_status = 'ON PROCESS';

try {
    $conn->begin_transaction();

    // Update request_status in request_coe_header where request_id matches
    $stmt1 = $conn->prepare("UPDATE request_coe_header SET request_status = ? WHERE request_id = ?");
    $stmt1->bind_param("ss", $request_status, $request_id);
    $stmt1->execute();

    // LOGS
    $stmt2 = $conn->prepare("INSERT INTO request_coe_logs
            (request_id, employee_id, action_remarks, request_status, action_by, action_date, action_time)
            VALUES (?,?,?,?,?,?,?)");
    $stmt2->bind_param("sssssss", $request_id, $employee_id, $onprocess_remarks, $req_status, $tagged_by, $requested_date, $requested_time);
    $stmt2->execute();

    $conn->commit();
    echo "Success";
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo "Error: " . $e->getMessage();
}
