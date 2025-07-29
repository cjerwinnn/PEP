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

$approver_level = $_POST['approver_level'];
$approval_remarks = $_POST['approval_remarks'];
$req_status = 'APPROVED';

$level_status = 'Level [' . $approver_level . '] ' . $req_status;

try {
    $conn->begin_transaction();

    $stmt = $conn->prepare("CALL COE_APPROVAL_CHECKCOUNT(?)");
    $stmt->bind_param("s", $request_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $count_notApproved = $row['not_approved_count'];
    $result->free();
    $stmt->close();

    if ($count_notApproved == 1) {
        $stmt1 = $conn->prepare("UPDATE request_coe_header SET request_status = 'APPROVED' WHERE request_id = ?");
        $stmt1->bind_param("s", $request_id);
        $stmt1->execute();

        $Automated_Remarks = 'All approvers have approved the request.';
        $Automated_taggedby = 'System Generated.';

        $stmt2 = $conn->prepare("INSERT INTO request_coe_logs
            (request_id, employee_id, action_remarks, request_status, action_by, action_date, action_time)
            VALUES (?,?,?,?,?,?,?)");
        $stmt2->bind_param("sssssss", $request_id, $employee_id, $Automated_Remarks, $req_status, $Automated_taggedby, $requested_date, $requested_time);
        $stmt2->execute();
    }

    $stmt1 = $conn->prepare("UPDATE request_coe_approvals_details SET tagged_remarks = ?, tagged_status = ?, tagged_date = ?, tagged_time = ? WHERE request_id = ? AND approver_employeeid = ?");
    $stmt1->bind_param("ssssss", $approval_remarks, $req_status, $requested_date, $requested_time, $request_id, $tagged_by);
    $stmt1->execute();

    // LOGS
    $stmt2 = $conn->prepare("INSERT INTO request_coe_logs
            (request_id, employee_id, action_remarks, request_status, action_by, action_date, action_time)
            VALUES (?,?,?,?,?,?,?)");
    $stmt2->bind_param("sssssss", $request_id, $employee_id, $approval_remarks, $level_status, $tagged_by, $requested_date, $requested_time);
    $stmt2->execute();

    $conn->commit();

    echo "Success";
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo "Error: " . $e->getMessage();
}
